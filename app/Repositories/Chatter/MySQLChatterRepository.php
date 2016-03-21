<?php

namespace App\Repositories\Chatter;

use DB;
use App\Channel;
use App\Chatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Contracts\Repositories\ChatterRepository;

class MySQLChatterRepository implements ChatterRepository
{
    /**
     * @var Chatter
     */
    private $model;

    /**
     * The current page for paginator.
     *
     * @var int
     */
    private $page = 1;

    /**
     * How many records per page for the paginator.
     *
     * @var int
     */
    private $perPage = 0;

    public function __construct(Chatter $model)
    {
        $this->model = $model;
    }

    /**
     * Setup pagination for results.
     *
     * @param int $page
     * @param int $limit
     *
     * @return $this
     */
    public function paginate($page = 1, $limit = 100)
    {
        $this->perPage = (int) $limit;
        $this->page = (int) $page;

        return $this;
    }

    /**
    * Find a single chatter by their handle and which users owns them.
    *
    * @param Channel $channel
    * @param $handle
    * @return array
    */
    public function findByHandle(Channel $channel, $handle)
    {
        $sub = $this->chattersQuery($channel);
        $sub->where('hidden', '!=', true);

        $query = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->mergeBindings($sub)
                ->where('handle', '=', $handle);

        return $query->first();
    }

    /**
     * Get all chatters belonging to a channel.
     *
     * @param Channel $channel
     * @param boolean $showHidden
     * @param boolean $showMod
     * @return Collection
     */
    public function allForChannel(Channel $channel, $showHidden = false, $showMod = false)
    {
        $sub = $this->chattersQuery($channel);

        if ($showHidden === false) {
            $sub->where('hidden', '!=', true);
        }

        if ($showMod === false) {
            $sub->orWhere('moderator', '!=', true);
        }

        $query = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->mergeBindings($sub);

        if ($this->perPage > 0) {
            return $query->paginate($this->perPage, ['*'], 'page', $this->page);
        }

        return new Collection($query->get());
    }

    /**
     * Get all mods belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allModsForChannel(Channel $channel)
    {
        return (new Collection(DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->where('moderator', '=', true)
            ->get()))->keyBy('handle');
    }

    /**
     * Remove a moderator from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeMod(Channel $channel, $handle)
    {
        if ($handle instanceof Collection) {
            $handle = $handle->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('handle', (array) $handle)
            ->update([ 'moderator' => false ]);
    }

    /**
     * Add a moderator to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addMod(Channel $channel, $handle)
    {
        if ($handle instanceof Collection) {
            $handle = $handle->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('handle', (array) $handle)
            ->update([ 'moderator' => true ]);
    }

    /**
     * Get all admins belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allAdminsForChannel(Channel $channel)
    {
        return (new Collection(DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->where('administrator', '=', true)
            ->get()))->keyBy('handle');
    }

    /**
     * Remove an admin from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeAdmin(Channel $channel, $handle)
    {
        if ($handle instanceof Collection) {
            $handle = $handle->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('handle', (array) $handle)
            ->update([ 'administrator' => false ]);
    }

    /**
     * Add an administor to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addAdmin(Channel $channel, $handle)
    {
        if ($handle instanceof Collection) {
            $handle = $handle->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('handle', (array) $handle)
            ->update([ 'administrator' => true ]);
    }

    /**
     * Delete a chatter, will only delete moderators.
     *
     * @param Channel $channel
     * @param $handle
     *
     * @return bool
     */
    public function deleteChatter(Channel $channel, $handle)
    {
        if ($handle instanceof Collection) {
            $handle = $handle->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('handle', (array) $handle)
            ->delete();
    }

    /**
     * Delete a channel. This only deletes the indexes.
     *
     * @param Channel $channel
     */
    public function deleteChannel(Channel $channel)
    {

    }

    /**
     * Update/Create a chatter.
     *
     * @param Channel $channel
     * @param string|array $handles
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateChatter(Channel $channel, $handle, $minutes = 0, $points = 0)
    {
        if (! $handle instanceof Collection) {
            $handle = new Collection($handle);
        }

        $exists = new Collection();

        $handle->chunk(100)->each(function ($handles) use (&$exists, $channel) {
            $result = DB::table('chatters')->where('channel_id', '=', $channel->id)->whereIn('handle', $handles)->lists('handle');
            $exists = $exists->merge($result);
        });

        $notExists = $handle->diff($exists);

        DB::beginTransaction();

        foreach ($exists as $handle) {
            DB::table('chatters')
                ->where('channel_id', '=', $channel->id)
                ->where('handle', '=', $handle)
                ->update([
                    'points' => DB::raw("points + {$points}"),
                    'minutes' => DB::raw("minutes + {$minutes}")
                ]);
        }

        foreach ($notExists as $handle) {
            DB::table('chatters')
                ->insert([
                    'channel_id' => $channel->id,
                    'handle'     => $handle,
                    'points'     => $points,
                    'minutes'    => $minutes
                ]);
        }

        DB::commit();
    }

    /**
     * Update/Create a moderator.
     *
     * @param Channel $channel
     * @param string|array $handles
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateModerator(Channel $channel, $handle, $minutes = 0, $points = 0)
    {
        $this->updateChatter($channel, $handle, $minutes, $points);
        $this->addMod($channel, $handle);
    }

    private function chattersQuery(Channel $channel)
    {
        return DB::table(DB::raw('chatters, (SELECT @curr := null, @prev := null, @rank := 0) sel1'))
            ->select(DB::raw('*, @prev := @curr, @curr := FLOOR(points), @rank := IF(@prev = @curr, @rank, @rank+1) AS rank'))
            ->where('channel_id', '=', $channel->id)
            ->orderBy('points', 'desc');
    }
}

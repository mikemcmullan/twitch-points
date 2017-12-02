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
                ->where('username', '=', $handle);

        return $query->first();
    }

    public function findByTwitchId(Channel $channel, $ids)
    {
        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $ids)
            ->get();
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
            ->get()))->keyBy('twitch_id');
    }

    /**
     * Remove a moderator from a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function removeMod(Channel $channel, $twitchId)
    {
        if ($twitchId instanceof Collection) {
            $twitchId = $twitchId->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $twitchId)
            ->update([ 'moderator' => false ]);
    }

    /**
     * Add a moderator to a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function addMod(Channel $channel, $twitchId)
    {
        if ($twitchId instanceof Collection) {
            $twitchId = $twitchId->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $twitchId)
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
            ->get()))->keyBy('username');
    }

    /**
     * Remove an admin from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeAdmin(Channel $channel, $twitchId)
    {
        if ($twitchId instanceof Collection) {
            $twitchId = $twitchId->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $twitchId)
            ->update([ 'administrator' => false ]);
    }

    /**
     * Add an administor to a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function addAdmin(Channel $channel, $twitchId)
    {
        if ($twitchId instanceof Collection) {
            $twitchId = $twitchId->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $twitchId)
            ->update([ 'administrator' => true ]);
    }

    /**
     * Delete a chatter, will only delete moderators.
     *
     * @param Channel $channel
     * @param $username
     *
     * @return bool
     */
    public function deleteChatter(Channel $channel, $twitchId)
    {
        if ($twitchId instanceof Collection) {
            $twitchId = $twitchId->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('twitch_id', (array) $twitchId)
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
     * Create a new chatter.
     *
     * @param Channel $channel
     * @param string|array $username
     * @param int $minutes
     * @param int $points
     * @return array
     */
    public function newChatters(Channel $channel, $chatters, $minutes = 0, $points = 0)
    {
        if (! $chatters instanceof Collection) {
            $chatters = new Collection($chatters);
        }

        DB::beginTransaction();

        $chatters->each(function ($chatter) use ($channel, $minutes, $points) {
            DB::table('chatters')
                ->insert([
                    'channel_id'    => $channel->id,
                    'twitch_id'     => $chatter['twitch_id'],
                    'username'      => $chatter['username'],
                    'display_name'  => $chatter['display_name'],
                    'points'        => $points,
                    'minutes'       => $minutes
                ]);
        });

        DB::commit();
    }

    /**
     * Update a chatter.
     *
     * @param Channel $channel
     * @param array $username
     * @param int $minutes
     * @param int $points
     * @return array
     */
    public function updateChatters(Channel $channel, $chatters, $minutes = 0, $points = 0)
    {
        if (! $chatters instanceof Collection) {
            $chatters = new Collection($chatters);
        }

        DB::beginTransaction();

        $chatters->each(function ($chatter) use ($channel, $minutes, $points) {
            DB::table('chatters')
                ->where('id', '=', $chatter['id'])
                ->update([
                    'twitch_id'     => $chatter['twitch_id'],
                    'username'      => $chatter['username'],
                    'display_name'  => $chatter['display_name'],
                    'points'        => DB::raw("points + {$points}"),
                    'minutes'       => DB::raw("minutes + {$minutes}")
                ]);
        });

        DB::commit();
    }

    /**
     * Update/Create a moderator.
     *
     * @param Channel $channel
     * @param $username
     * @param int $minutes
     * @param int $points
     * @return array
     */
    public function updateModerator(Channel $channel, $user, $minutes = 0, $points = 0)
    {
        $this->addMod($channel, $user['id']);
        return $this->updateChatter($channel, $user, $minutes, $points);
    }

    private function chattersQuery(Channel $channel)
    {
        return DB::table(DB::raw('chatters, (SELECT @curr := 1, @prev := null, @rank := 0) sel1'))
            ->select(DB::raw('*, @prev := @curr, @curr := FLOOR(points), @rank := IF(@prev = @curr, @rank, @rank+1) AS rank'))
            ->where('channel_id', '=', $channel->id)
            ->orderBy('points', 'desc');
    }
}

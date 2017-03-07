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
            ->get()))->keyBy('username');
    }

    /**
     * Remove a moderator from a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function removeMod(Channel $channel, $username)
    {
        if ($username instanceof Collection) {
            $username = $username->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('username', (array) $username)
            ->update([ 'moderator' => false ]);
    }

    /**
     * Add a moderator to a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function addMod(Channel $channel, $username)
    {
        if ($username instanceof Collection) {
            $username = $username->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('username', (array) $username)
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
    public function removeAdmin(Channel $channel, $username)
    {
        if ($username instanceof Collection) {
            $username = $username->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('username', (array) $username)
            ->update([ 'administrator' => false ]);
    }

    /**
     * Add an administor to a channel.
     *
     * @param Channel $channel
     * @param array $username
     */
    public function addAdmin(Channel $channel, $username)
    {
        if ($username instanceof Collection) {
            $username = $username->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('username', (array) $username)
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
    public function deleteChatter(Channel $channel, $username)
    {
        if ($username instanceof Collection) {
            $username = $username->toArray();
        }

        return DB::table('chatters')
            ->where('channel_id', '=', $channel->id)
            ->whereIn('username', (array) $username)
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
     * @param string|array $username
     * @param int $minutes
     * @param int $points
     * @return array
     */
    public function updateChatter(Channel $channel, $username, $minutes = 0, $points = 0)
    {
        if (! $username instanceof Collection) {
            $username = new Collection($username);
        }

        $exists = new Collection();

        $username->chunk(500)->each(function ($usernames) use (&$exists, $channel) {
            $result = DB::table('chatters')->where('channel_id', '=', $channel->id)->whereIn('username', $usernames)->get();
            $exists = $exists->merge($result);
        });

        $notExists = $username->diff($exists->pluck('username'))->map(function ($username) {
            $user = getUserFromRedis($username);

            if (! $user) {
                return [
                    'user_id'       => null,
                    'username'      => $username,
                    'display_name'  => null
                ];
            }

            return [
                'user_id'       => $user['user_id'],
                'username'      => $user['username'],
                'display_name'  => $user['display_name']
            ];
        });

        $exists = $exists->map(function ($chatter) {
            $user = getUserFromRedis($chatter['username']);

            if ($user) {
                $chatter['service_id'] = $user['user_id'];
                $chatter['display_name'] = $user['display_name'];
            }

            return $chatter;
        });

        DB::beginTransaction();

        foreach ($exists as $chatter) {
            DB::table('chatters')
                ->where('id', '=', $chatter['id'])
                ->update([
                    'service_id'    => $chatter['service_id'],
                    'display_name'  => $chatter['display_name'],
                    'points'        => DB::raw("points + {$points}"),
                    'minutes'       => DB::raw("minutes + {$minutes}")
                ]);
        }

        foreach ($notExists as $username) {
            DB::table('chatters')
                ->insert([
                    'channel_id'    => $channel->id,
                    'service_id'    => $username['user_id'],
                    'username'      => $username['username'],
                    'display_name'  => $username['display_name'],
                    'points'        => $points,
                    'minutes'       => $minutes
                ]);
        }

        DB::commit();

        return [
            'points' => $points,
            'minutes' => $minutes,
            'existing' => $exists,
            'new' => $notExists
        ];
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
    public function updateModerator(Channel $channel, $username, $minutes = 0, $points = 0)
    {
        $this->addMod($channel, $username);
        return $this->updateChatter($channel, $username, $minutes, $points);
    }

    private function chattersQuery(Channel $channel)
    {
        return DB::table(DB::raw('chatters, (SELECT @curr := 1, @prev := null, @rank := 0) sel1'))
            ->select(DB::raw('*, @prev := @curr, @curr := FLOOR(points), @rank := IF(@prev = @curr, @rank, @rank+1) AS rank'))
            ->where('channel_id', '=', $channel->id)
            ->orderBy('points', 'desc');
    }
}

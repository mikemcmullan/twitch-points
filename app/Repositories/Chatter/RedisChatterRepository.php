<?php

namespace App\Repositories\Chatter;

use App\Channel;
use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Redis\Database;
use Illuminate\Support\Collection;
use App\Contracts\Repositories\ChatterRepository;
use Predis\Pipeline\Pipeline;

class RedisChatterRepository implements ChatterRepository
{
    /**
     * Chat Key Format
     */
    const CHAT_KEY_FORMAT = 'chat:%s:%s';

    /**
     * Chat Index Key Format
     */
    const CHAT_INDEX_KEY_FORMAT = 'chattersIndex:%s';

    /**
     * Admin Index Key Format
     */
    const ADMIN_INDEX_KEY_FORMAT = 'adminsIndex:%s';

    /**
     * Mod Index Key Format
     */
    const MOD_INDEX_KEY_FORMAT = 'modsIndex:%s';

    /**
     * Rankings Index Key Format
     */
    const RANKING_INDEX_KEY_FORMAT = 'rankingsIndex:%s';

    /**
     * @var Database
     */
    private $redis;

    /**
     * @var
     */
    private $channel;

    /**
     * @var ConfigRepository
     */
    private $config;

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

    /**
     * @param Database $redis
     * @param ConfigRepository $config
     */
    public function __construct(Database $redis, ConfigRepository $config)
    {
        $this->redis = $redis->connection();
        $this->config = $config;
    }

    /**
     * Set the giveaway status for a user.
     *
     * @param $status
     * @return mixed
     */
    public function setGiveAwayStatus(Channel $channel, $handle, $status)
    {
        $key = $this->makeKey($channel['id'], $handle);

        return $this->redis->hset($key, 'giveaway', (bool) $status);
    }

    /**
     * Get the time the points system was last updated for a channel.
     *
     * @return string
     */
    public function lastUpdate(Channel $channel)
    {
        $key    = 'last_update:' . $channel['id'];
        $value  = $this->redis->get($key);

        if ($value) {
            return Carbon::parse($value);
        }
    }

    /**
     * Set the time the points system for a channel was last updated.
     *
     * @param Carbon $time
     *
     * @return mixed
     */
    public function setLastUpdate(Channel $channel, Carbon $time)
    {
        $key = 'last_update:' . $channel['id'];

        return $this->redis->set($key, $time->second(0)->toDateTimeString());
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
     * Get all chatters belonging to a channel.
     *
     * @param Channel $channel
     * @param boolean $showHidden
     * @param boolean $showMod
     * @return Collection
     */
    public function allForChannel(Channel $channel, $showHidden = false, $showMod = false)
    {
        $start = 0;
        $end = -1;

        if ($this->perPage > 0) {
            $start = $this->perPage * ($this->page - 1);
            $end = $start + $this->perPage - 1;
        }

        $chatters = $this->redis->zrevrange($this->makeChatIndexKey($channel['id']), $start, $end, 'WITHSCORES');

        $collection  = new Collection($this->mapUsers($chatters, $showHidden, $showMod));

        return $collection;
    }

    /**
     * Get the number of chatters a channel has.
     *
     * @param Channel $channel
     * @return int
     */
    public function getCountForChannel(Channel $channel)
    {
        return $this->redis->zcard($this->makeChatIndexKey($channel['id']));
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
        $key = $this->makeKey($channel['id'], $handle);

        $result = $this->redis->hgetall($key);

        if (empty($result)) {
            return;
        }

        $user = $this->mapUser($channel, $handle, $result);

        return $user;
    }

    /**
     * Delete a channel. This only deletes the indexes.
     *
     * @param Channel $channel
     */
    public function deleteChannel(Channel $channel)
    {
        $this->redis->del($this->makeModIndexKey($channel['id']));
        $this->redis->del($this->makeAdminIndexKey($channel['id']));
        $this->redis->del($this->makeChatIndexKey($channel['id']));

        return true;
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
        $viewer = $this->findByHandle($channel, $handle);

        if ($viewer) {
            $this->redis->del($viewer['key']);
            $this->redis->srem($this->makeModIndexKey($channel['id']), $viewer['key']);
            $this->redis->srem($this->makeAdminIndexKey($channel['id']), $viewer['key']);
            $this->redis->zrem($this->makeChatIndexKey($channel['id']), $viewer['key']);

            return true;
        }
    }

    /**
     * Update/Create a chatter.
     *
     * @param Channel $channel
     * @param string|array $handles
     * @param int $minutes
     * @param int $points
     */
    public function updateChatter(Channel $channel, $handles, $minutes = 0, $points = 0)
    {
        foreach ((array) $handles as $handle) {
            $key = $this->makeKey($channel['id'], $handle);

            $user = $this->findByHandle($channel, $handle);
            $newPointTotal = $this->calculatePointTotal($user['points'], $points);

            $this->updateRank($channel, $user, $newPointTotal);

            $this->redis->hset($key, 'points', $newPointTotal);
            $this->redis->zadd($this->makeChatIndexKey($channel['id']), $newPointTotal, $key);

            $this->redis->hincrby($key, 'minutes', $minutes);
            $this->redis->hset($key, 'updated', Carbon::now());
        }
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
    public function updateModerator(Channel $channel, $handles, $minutes = 0, $points = 0)
    {
        foreach ((array) $handles as $handle) {
            $this->addMod($channel, $handle);
            $this->updateChatter($channel, $handle, $minutes, $points);
        }
    }

    /**
     * Get all mods belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allModsForChannel(Channel $channel)
    {
        $mods = $this->redis->smembers($this->makeModIndexKey($channel['id']));
        $collection = new Collection($this->mapUsers($mods, true, true));

        return $collection;
    }

    /**
     * Remove a moderator from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeMod(Channel $channel, $handle)
    {
        $key = $this->makeKey($channel['id'], $handle);

        $this->redis->hset($key, 'mod', false);
        $this->redis->srem($this->makeModIndexKey($channel['id']), $key);
    }

    /**
     * Add a moderator to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addMod(Channel $channel, $handle)
    {
        $key = $this->makeKey($channel['id'], $handle);

        $this->redis->hset($key, 'mod', true);
        $this->redis->sadd($this->makeModIndexKey($channel['id']), $key);
    }

    /**
     * Get all admins belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allAdminsForChannel(Channel $channel)
    {
        $admins = $this->redis->smembers($this->makeAdminIndexKey($channel['id']));
        $collection = new Collection($this->mapUsers($admins, true, true));

        return $collection;
    }

    /**
     * Remove an admin from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeAdmin(Channel $channel, $handle)
    {
        $key = $this->makeKey($channel['id'], $handle);

        $this->redis->hset($key, 'admin', false);
        $this->redis->srem($this->makeAdminIndexKey($channel['id']), $key);
    }

    /**
     * Add an administor to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addAdmin(Channel $channel, $handle)
    {
        $key = $this->makeKey($channel['id'], $handle);

        $this->redis->hset($key, 'admin', true);
        $this->redis->sadd($this->makeAdminIndexKey($channel['id']), $key);
    }

    /**
     * Update the rank for a user.
     *
     * @param Channel $channel
     * @param array $user
     * @param int $newPointTotal
     */
    private function updateRank(Channel $channel, $user, $newPointTotal)
    {
        $key = $this->makeKey($channel['id'], $user['handle']);

        $scoreCheck = collect($this->redis->zrangebyscore($this->makeChatIndexKey($channel['id']), floor($user['points']), floor($user['points'])))
            ->filter(function ($chatter) use ($key) {
                return $key !== $chatter;
            });

        if ($scoreCheck->isEmpty()) {
            $this->redis->zrem($this->makeRankingIndexKey($channel['id']), floor($user['points']));
        }

        if (! $user['hide']) {
            $this->redis->zadd($this->makeRankingIndexKey($channel['id']), floor($newPointTotal), floor($newPointTotal));
        }
    }

    /**
     * Make redis key for getting a chatter.
     *
     * @param $channel
     * @param $handle
     * @return mixed
     */
    private function makeKey($channel, $handle)
    {
        return sprintf(self::CHAT_KEY_FORMAT, $channel, $handle);
    }

    /**
     * Make a redis key for the chatter index.
     *
     * @param $channel
     *
     * @return string
     */
    private function makeChatIndexKey($channel)
    {
        return sprintf(self::CHAT_INDEX_KEY_FORMAT, $channel);
    }

    /**
     * Make a redis key for the admin index.
     * @param $channel
     *
     * @return string
     */
    private function makeAdminIndexKey($channel)
    {
        return sprintf(self::ADMIN_INDEX_KEY_FORMAT, $channel);
    }

    /**
     * Make a redis key for the mod index.
     * @param $channel
     *
     * @return string
     */
    private function makeModIndexKey($channel)
    {
        return sprintf(self::MOD_INDEX_KEY_FORMAT, $channel);
    }

    /**
     * Make a redis key for the ranking index.
     * @param $channel
     *
     * @return string
     */
    private function makeRankingIndexKey($channel)
    {
        return sprintf(self::RANKING_INDEX_KEY_FORMAT, $channel);
    }

    /**
     * Map over uses to make an associative array containing
     * the original key, handle, channel and start_time.
     *
     * @param array $chatters
     * @param boolean $showHidden
     * @param boolean $showMod
     * @return array
     */
    private function mapUsers(array $chatters, $showHidden = false, $showMod = false)
    {
        $mappedUsers = [];

        foreach ($chatters as $key => $value) {
            if (is_string($key)) {
                $chatter = $key;
                $indexScore = $value;
            } else {
                $chatter = $value;
                $indexScore = false;
            }

            $key = $this->parseKey($chatter);

            $data = $this->redis->hgetall($chatter);

            if (empty($data)) {
                continue;
            }

            $rank     = $this->redis->zrevrank($this->makeRankingIndexKey($key['channel']), floor($data['points']));

            // Since redis rankings are 0 based add 1.
            if ($rank !== null) {
                $rank++;
            }

            $mod      = (bool) array_get($data, 'mod');
            $hide     = (bool) array_get($data, 'hide');
            $admin    = (bool) array_get($data, 'admin');
            $giveaway = (bool) array_get($data, 'giveaway');

            if (($showHidden === false && $hide) || ($showMod === false && $mod)) {
                continue;
            }

            $mappedUsers[$key['handle']] = [
                'key'     => $chatter,
                'handle'  => $key['handle'],
                'channel' => $key['channel'],
                'minutes' => $data['minutes'],
                'points'  => $data['points'],
                'rank'    => $rank,
                'updated' => $data['updated'],
                'mod'     => $mod,
                'hide'    => $hide,
                'admin'   => $admin,
                'giveaway'=> $giveaway
            ];
        }

        return $mappedUsers;
    }

    /**
     * Add addition information to a user. Key, handle and channel
     *
     * @param $channel
     * @param $handle
     * @param array $user
     * @return array
     */
    private function mapUser(Channel $channel, $handle, array $user)
    {
        $rank = $this->redis->zrevrank($this->makeRankingIndexKey($channel['id']), floor(array_get($user, 'points', 0)));

        $user['rank']     = $rank === null ? null : ++$rank;
        $user['points']   = array_get($user, 'points', 0);
        $user['key']      = $this->makeKey($channel['id'], $handle);
        $user['channel']  = $channel;
        $user['handle']   = $handle;
        $user['minutes']  = (int) array_get($user, 'minutes');
        $user['mod']      = (bool) array_get($user, 'mod');
        $user['hide']     = (bool) array_get($user, 'hide');
        $user['admin']    = (bool) array_get($user, 'admin');
        $user['giveaway'] = (bool) array_get($user, 'giveaway');

        return $user;
    }

    // private function mapMods(array $mods)
    // {
    //     $mappedMods = [];
    //
    //     foreach ($mods as $mod) {
    //         $key = $this->parseKey($mod);
    //         $data= $this->redis->hgetall($chatter);
    //         $mappedMods[$key['handle']] = $this->mapUser()
    //     }
    //
    //     return $mappedMods;
    // }

    /**
     * Parse a redis key to provide the channel and handle.
     *
     * @param $key
     * @return array
     */
    private function parseKey($key)
    {
        if ($this->channel === null) {
            $this->channel = substr($key, 5, strpos($key, ':', 5) - 5);
            $this->handlePrefix = sprintf(self::CHAT_KEY_FORMAT, $this->channel, '');
        }

        return [
            'channel' => $this->channel,
            'handle'  => substr($key, strlen($this->handlePrefix))
        ];
    }

    /**
     * Calculate point total.
     *
     * @param int $currentPoints
     * @param string|int $newPoints An addition or substraction sign will Indicate if we are
     *                          adding or substracting points. If none is provided we will add.
     *
     * @return int|float
     */
    private function calculatePointTotal($currentPoints, $newPoints)
    {
        switch (substr($newPoints, 0, 1)) {
            case '-':
                $value = $currentPoints - (float) substr($newPoints, 1);
                break;

            case '+':
                $newPoints = substr($newPoints, 1);

            default:
                $value = $currentPoints + (float) $newPoints;
                break;
        }

        return round($value, 3);
    }
}

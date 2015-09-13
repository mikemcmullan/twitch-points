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

class RedisChatterRepository implements ChatterRepository {

	/**
     * Chat Key Format
     */
    const CHAT_KEY_FORMAT = 'chat:%s:%s';

	/**
     * Chat Index Key Format
     */
    const CHAT_INDEX_KEY_FORMAT = 'chattersIndex:%s';

	/**
     * Mod Index Key Format
     */
    const MOD_INDEX_KEY_FORMAT = 'modsIndex:%s';

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
     * Get the time the points system was last updated for a channel.
     *
     * @return string
     */
    public function lastUpdate(Channel $channel)
    {
        $key    = 'last_update:' . $channel['id'];
        $value  = $this->redis->get($key);

        if ($value)
        {
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

        if ($this->perPage > 0)
        {
            $start = $this->perPage * ($this->page - 1);
            $end = $start + $this->perPage - 1;
        }

        $chatters = $this->redis->zrevrange($this->makeChatIndexKey($channel['id']), $start, $end);
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

        if (empty($result))
        {
            return;
        }

        return $this->mapUser($channel, $handle, $result);
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
            $this->redis->zrem($this->makeChatIndexKey($channel['id']), $viewer['key']);
            $this->redis->zrem($this->makeModIndexKey($channel['id']), $viewer['key']);

            return true;
        }
    }

    /**
     * Update/Create a chatter.
     *
     * @param Channel $channel
     * @param $handle
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateChatter(Channel $channel, $handle, $minutes = 0, $points = 0, Pipeline $pipe = null)
    {
        $key = $this->makeKey($channel['id'], $handle);

        if ($pipe === null)
        {
            $pipe = $this->redis;
        }

        $pipe->zincrby($this->makeChatIndexKey($channel['id']), $points, $key);
        $pipe->hincrbyfloat($key, 'points', $points);
        $pipe->hincrby($key, 'minutes', $minutes);
        $pipe->hset($key, 'updated', Carbon::now());
    }

	/**
     * Update/Create a group of chatters.
     *
     * @param Channel $channel
     * @param array $chatters
     * @param $minutes
     * @param $points
     */
    public function updateChatters(Channel $channel, array $chatters, $minutes = 0, $points = 0)
    {
        $this->redis->pipeline(function($pipe) use($channel, $chatters, $minutes, $points)
        {
            foreach ($chatters as $chatter)
            {
                $this->updateChatter($channel, $chatter, $minutes, $points, $pipe);
            }
        });
    }

	/**
     * Update/Create a moderator.
     *
     * @param Channel $channel
     * @param $handle
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateModerator(Channel $channel, $handle, $minutes = 0, $points = 0, Pipeline $pipe = null)
    {
        $key = $this->makeKey($channel['id'], $handle);

        if ($pipe === null)
        {
            $pipe = $this->redis;
        }

        $pipe->zincrby($this->makeModIndexKey($channel['id']), $points, $key);
        $pipe->zincrby($this->makeChatIndexKey($channel['id']), $points, $key);
        $pipe->hincrbyfloat($key, 'points', $points);
        $pipe->hincrby($key, 'minutes', $minutes);
        $pipe->hset($key, 'mod', true);
        $pipe->hset($key, 'updated', Carbon::now());
    }

    /**
     * Update/Create a group of moderators.
     *
     * @param Channel $channel
     * @param array $chatters
     * @param $minutes
     * @param $points
     */
    public function updateModerators(Channel $channel, array $chatters, $minutes = 0, $points = 0)
    {
        $this->redis->pipeline(function($pipe) use($channel, $chatters, $minutes, $points)
        {
            foreach ($chatters as $chatter)
            {
                $this->updateModerator($channel, $chatter, $minutes, $points, $pipe);
            }
        });
    }

	/**
     * Update rankings for chatters.
     *
     * @param Channel $channel
     * @param array $chatters
     */
    public function updateRankings(Channel $channel, array $chatters)
    {
        $this->redis->pipeline(function($pipe) use($channel, $chatters)
        {
            foreach ($chatters as $chatter)
            {
                $key = $this->makeKey($channel['id'], $chatter['handle']);

                $pipe->hset($key, 'rank', $chatter['rank']);
            }
        });
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

        foreach ($chatters as $chatter)
        {
            $key = $this->parseKey($chatter);

            $data = $this->redis->hgetall($chatter);
            $rank = isset($data['rank']) ? $data['rank'] : 0;
            $mod  = (bool) array_get($data, 'mod');
            $hide = (bool) array_get($data, 'hide');

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
                'hide'    => $hide
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
    private function mapUser($channel, $handle, array $user)
    {
        $user['key']    = $this->makeKey($channel['id'], $handle);
        $user['channel']= $channel;
        $user['handle'] = $handle;
        $user['mod']    = (bool) array_get($user, 'mod');
        $user['hide']   = (bool) array_get($user, 'hide');

        return $user;
    }

    /**
     * Parse a redis key to provide the channel and handle.
     *
     * @param $key
     * @return array
     */
    private function parseKey($key)
    {
        if ($this->channel === null)
        {
            $this->channel = substr($key, 5, strpos($key, ':', 5) - 5);
            $this->handlePrefix = sprintf(self::CHAT_KEY_FORMAT, $this->channel, '');
        }

        return [
            'channel' => $this->channel,
            'handle'  => substr($key, strlen($this->handlePrefix))
        ];
    }

}
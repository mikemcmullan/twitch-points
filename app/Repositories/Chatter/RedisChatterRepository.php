<?php

namespace App\Repositories\Chatter;

use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Redis\Database;
use Illuminate\Support\Collection;

/**
 * Class RedisChatUserRepository
 * @package App\Repositories\ChatUsers*
 */
class RedisChatterRepository extends AbstractChatterRepository implements ChatterRepository {

    /**
     * @var Database
     */
    private $redis;

    /**
     * @var
     */
    private $channel;

    /**
     * @var string
     */
    private $keyFormat = 'chat:{channel}:{handle}';

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @param Database $redis
     * @param ConfigRepository $config
     */
    public function __construct(Database $redis, ConfigRepository $config)
    {
        parent::__construct();

        $this->redis = $redis;
        $this->config = $config;
    }

    /**
     * Get all users for a channel from redis.
     *
     * @param $channel
     * @return Collection
     */
    public function users($channel)
    {
        $users = $this->redis->keys($this->makeKey($channel, '*'));

        return new Collection($this->mapUsers($users));
    }

    /**
     * Get a single user.
     *
     * @param $channel
     * @param $handle
     * @return array
     */
    public function user($channel, $handle)
    {
        $key = $this->makeKey($channel, $handle);

        $result = $this->redis->hgetall($key);

        if (empty($result))
        {
            return [];
        }

        return $this->mapUser($channel, $handle, $result);
    }

    /**
     * Create a new chat user.
     *
     * @param $channel
     * @param $handle
     * @return
     */
    public function create($channel, $handle)
    {
        $key = $this->makeKey($channel, $handle);

        return $this->redis->hmset($key, [
            'start_time'            => $this->time,
            'total_minutes_online'  => 0,
            'points'                => $this->config->get('twitch.points.award_new')
        ]);
    }

    /**
     * Create many chat users.
     *
     * @param $channel
     * @param Collection $handles
     */
    public function createMany($channel, Collection $handles)
    {
        $redis = $this->redis;

        $this->redis->pipeline(function($pipe) use($channel, $handles)
        {
            $this->redis = $pipe;
            foreach ($handles as $handle)
            {
                $this->create($channel, $handle);
            }
        });

        $this->redis = $redis;
    }

    /**
     * Update an existing chat user.
     *
     * @param $channel
     * @param $handle
     * @param int $totalMinutesOnline
     * @param int $points
     */
    public function update($channel, $handle, $totalMinutesOnline = 0, $points = 0)
    {
        $key = $this->makeKey($channel, $handle);

        $this->redis->hset($key, 'start_time', $this->time);
        $this->redis->hincrbyfloat($key, 'points', $points);
        $this->redis->hincrby($key, 'total_minutes_online', $totalMinutesOnline);
    }

    /**
     * Update many users.
     *
     * @param $channel
     * @param Collection $users
     */
    public function updateMany($channel, Collection $users)
    {
        $redis = $this->redis;

        $this->redis->pipeline(function($pipe) use($channel, $users)
        {
            $this->redis = $pipe;
            foreach ($users as $user)
            {
                $this->update($channel, $user['handle'], $user['total_minutes_online'], $user['points']);
            }
        });

        $this->redis = $redis;
    }

    /**
     * Set a user to offline.
     *
     * @param $channel
     * @param $handle
     * @return mixed
     */
    public function offline($channel, $handle)
    {
        $key = $this->makeKey($channel, $handle);

        return $this->redis->hset($key, 'start_time', null);
    }

    /**
     * Offline many users.
     *
     * @param $channel
     * @param Collection $handles
     */
    public function offlineMany($channel, Collection $handles)
    {
        $redis = $this->redis;

        $this->redis->pipeline(function($pipe) use($channel, $handles)
        {
            $this->redis = $pipe;
            foreach ($handles as $handle)
            {
                $this->offline($channel, $handle['handle']);
            }
        });

        $this->redis = $redis;
    }

    /**
     * Make redis key.
     *
     * @param $channel
     * @param $handle
     * @return mixed
     */
    private function makeKey($channel, $handle)
    {
        return str_replace(['{channel}', '{handle}'], [$channel, $handle], $this->keyFormat);
    }

    /**
     * Map over uses to make an associative array containing
     * the original key, handle, channel and start_time.
     *
     * @param $users
     * @return array
     */
    private function mapUsers(array $users)
    {
        $mappedUsers = [];

        foreach ($users as $user)
        {
            $key = $this->parseKey($user);

            $mappedUsers[$key['handle']] = [
                'key'        => $user,
                'handle'     => $key['handle'],
                'channel'    => $key['channel'],
                'start_time' => $this->redis->hget($user, 'start_time'),
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
        $user['key']    = $this->makeKey($channel, $handle);
        $user['channel']= $channel;
        $user['handle'] = $handle;

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
            $this->handlePrefix = str_replace(
                ['{channel}', '{handle}'],
                [$this->channel, ''],
                $this->keyFormat
            );
        }

        return [
            'channel' => $this->channel,
            'handle'  => substr($key, strlen($this->handlePrefix))
        ];
    }

}
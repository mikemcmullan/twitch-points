<?php

namespace App\Repositories\ChatUsers;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

/**
 * Class MySqlChatUserRepository
 * @package App\Repositories\ChatUsers
 */
class MySqlChatUserRepository extends AbstractChatUserRepository implements ChatUserRepository {

    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @var Repository
     */
    private $config;

    /**
     * @param DatabaseManager $db
     * @param ConfigRepository $config
     */
    public function __construct(DatabaseManager $db, ConfigRepository $config)
    {
        parent::__construct();

        $this->db = $db;
        $this->config = $config;
    }

    /**
     * @param $channel
     * @return Collection
     */
    public function users($channel)
    {
        $users = $this->db->table('chat_users')->where('channel', '=', $channel)->get();

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
        return $this->db->table('chat_users')
            ->where('channel', '=', $channel)
            ->where('handle', '=', $handle)
            ->first();
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
        return $this->db->table('chat_users')->insert([
            'channel'       => $channel,
            'handle'        => $handle,
            'start_time'    => $this->time,
            'points'        => $this->config->get('twitch.points.award_new')
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
        $this->db->transaction(function() use($channel, $handles)
        {
            foreach ($handles as $handle)
            {
                $this->create($channel, $handle);
            }
        });
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
        return $this->db->table('chat_users')
            ->where('channel', '=', $channel)
            ->where('handle', '=', $handle)
            ->update([
                'start_time'            => $this->time,
                'total_minutes_online'  => $this->db->raw('total_minutes_online + ' . $totalMinutesOnline),
                'points'                => $this->db->raw('points + ' . $points)
            ]);
    }

    /**
     * Update many users.
     *
     * @param $channel
     * @param Collection $users
     */
    public function updateMany($channel, Collection $users)
    {
        $this->db->transaction(function() use($channel, $users)
        {
            foreach ($users as $user)
            {
                $this->update($channel, $user['handle'], $user['total_minutes_online'], $user['points']);
            }
        });
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
        return $this->db->table('chat_users')
            ->where('channel', '=', $channel)
            ->where('handle', '=', $handle)
            ->update([
                'start_time' => null
            ]);
    }

    /**
     * Offline many users.
     *
     * @param $channel
     * @param Collection $handles
     */
    public function offlineMany($channel, Collection $handles)
    {
        $this->db->transaction(function() use($channel, $handles)
        {
            foreach ($handles as $handle)
            {
                $this->offline($channel, $handle['handle']);
            }
        });
    }

	/**
     * Offline all the users for a channel.
     *
     * @param $channel
     * @return mixed
     */
    public function offlineAllForChannel($channel)
    {
        return $this->db->table('chat_users')
            ->where('channel', '=', $channel)
            ->update([
                'start_time' => null
            ]);
    }

    /**
     * @param $users
     * @return array
     */
    private function mapUsers($users)
    {
        $mappedUsers = [];

        foreach ($users as $user)
        {
            $mappedUsers[$user['handle']] = $user;
        }

        return $mappedUsers;
    }
}
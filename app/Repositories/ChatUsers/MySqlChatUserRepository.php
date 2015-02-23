<?php

namespace App\Repositories\ChatUsers;

use App\User;
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
     * @param User $user
     * @return Collection
     */
    public function users(User $user)
    {
        $users = $this->db->table('chat_users')->where('user_id', '=', $user['id'])->get();

        return new Collection($this->mapUsers($users));
    }

    /**
     * Get a single user.
     *
     * @param User $user
     * @param $handle
     * @return array
     */
    public function user(User $user, $handle)
    {
        return $this->db->table('chat_users')
            ->where('user_id', '=', $user['id'])
            ->where('handle', '=', $handle)
            ->first();
    }

    /**
     * Create a new chat user.
     *
     * @param User $user
     * @param $handle
     * @return
     */
    public function create(User $user, $handle)
    {
        return $this->db->table('chat_users')->insert([
            'user_id'       => $user['id'],
            'handle'        => $handle,
            'start_time'    => $this->time,
            'points'        => $this->config->get('twitch.points.award_new')
        ]);
    }

    /**
     * Create many chat users.
     *
     * @param User $user
     * @param Collection $handles
     */
    public function createMany(User $user, Collection $handles)
    {
        $this->db->transaction(function() use($user, $handles)
        {
            foreach ($handles as $handle)
            {
                $this->create($user, $handle);
            }
        });
    }

    /**
     * Update an existing chat user.
     *
     * @param User $user
     * @param $handle
     * @param int $totalMinutesOnline
     * @param int $points
     */
    public function update(User $user, $handle, $totalMinutesOnline = 0, $points = 0)
    {
        return $this->db->table('chat_users')
            ->where('user_id', '=', $user['id'])
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
     * @param User $user
     * @param Collection $users
     */
    public function updateMany(User $user, Collection $users)
    {
        $this->db->transaction(function() use($user, $users)
        {
            foreach ($users as $chatUser)
            {
                $this->update($user, $chatUser['handle'], $chatUser['total_minutes_online'], $chatUser['points']);
            }
        });
    }

    /**
     * Set a user to offline.
     *
     * @param User $user
     * @param $handle
     * @return mixed
     */
    public function offline(User $user, $handle)
    {
        return $this->db->table('chat_users')
            ->where('user_id', '=', $user['id'])
            ->where('handle', '=', $handle)
            ->update([
                'start_time' => null
            ]);
    }

    /**
     * Offline many users.
     *
     * @param User $user
     * @param Collection $handles
     */
    public function offlineMany(User $user, Collection $handles)
    {
        $this->db->transaction(function() use($user, $handles)
        {
            foreach ($handles as $handle)
            {
                $this->offline($user, $handle['handle']);
            }
        });
    }

	/**
     * Offline all the users for a channel.
     *
     * @param User $user
     * @return mixed
     */
    public function offlineAllForChannel(User $user)
    {
        return $this->db->table('chat_users')
            ->where('user_id', '=', $user['id'])
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
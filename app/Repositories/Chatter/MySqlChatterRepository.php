<?php

namespace App\Repositories\Chatter;

use App\Contracts\Repositories\ChatterRepository;
use App\User;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

/**
 * Class MySqlChatUserRepository
 * @package App\Repositories\ChatUsers
 */
class MySqlChatterRepository extends AbstractChatterRepository implements ChatterRepository {

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
     *
     * @return Collection
     */
    public function allForUser(User $user)
    {
        $users = $this->db->table('chatters')->where('user_id', '=', $user['id'])->get();

        return new Collection($this->mapUsers($users));
    }

    /**
     * @param User $user
     * @param $handle
     * @return array
     */
    public function findByHandle(User $user, $handle)
    {
        return $this->db->table('chatters')
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
        return $this->db->table('chatters')->insert([
            'user_id'       => $user['id'],
            'handle'        => $handle,
            'start_time'    => $this->time,
            'created_at'    => $this->time,
            'updated_at'    => $this->time
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
        $this->db->beginTransaction();

        foreach ($handles as $handle)
        {
            $this->create($user, $handle);
        }

        $this->db->commit();
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
        return $this->db->table('chatters')
            ->where('user_id', '=', $user['id'])
            ->where('handle', '=', $handle)
            ->update([
                'start_time'            => $this->time,
                'total_minutes_online'  => $this->db->raw('total_minutes_online + ' . $totalMinutesOnline),
                'points'                => $this->db->raw('points + ' . $points),
                'updated_at'            => $this->time
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
        $this->db->beginTransaction();

        foreach ($users as $chatUser)
        {
            $this->update($user, $chatUser->handle, $chatUser->total_minutes_online, $chatUser->points);
        }

        $this->db->commit();
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
        return $this->db->table('chatters')
            ->where('user_id', '=', $user['id'])
            ->where('handle', '=', $handle)
            ->update([
                'start_time' => null,
                'updated_at' => $this->time
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
        $this->db->beginTransaction();

        foreach ($handles as $handle)
        {
            $this->offline($user, $handle->handle);
        }

        $this->db->commit();
    }

	/**
     * Offline all the users for a channel.
     *
     * @param User $user
     * @return mixed
     */
    public function offlineAllForChannel(User $user)
    {
        return $this->db->table('chatters')
            ->where('user_id', '=', $user['id'])
            ->update([
                'start_time' => null,
                'updated_at' => $this->time
            ]);
    }

    /**
     * @param $chatterId
     * @param $rank
     *
     * @return mixed
     */
    public function updateRank($chatterId, $rank)
    {
        return $this->db->table('chatters')
            ->where('id', $chatterId)
            ->update([
                'rank' => $rank
            ]);
    }

    /**
     * @param Collection $chatters
     *
     * @return mixed|void
     */
    public function updateRankMany(Collection $chatters)
    {
        $this->db->beginTransaction();

        foreach ($chatters as $chatter)
        {
            $this->updateRank($chatter->id, $chatter->rank);
        }

        $this->db->commit();
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
            $mappedUsers[$user->handle] = $user;
        }

        return $mappedUsers;
    }
}
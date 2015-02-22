<?php

namespace App\Repositories\ChatUsers;

use Illuminate\Support\Collection;

interface ChatUserRepository {

    /**
     * @param $channel
     * @return Collection
     */
    public function users($channel);

    /**
     * Get a single user.
     *
     * @param $channel
     * @param $handle
     * @return array
     */
    public function user($channel, $handle);

    /**
     * Create a new chat user.
     *
     * @param $channel
     * @param $handle
     * @return
     */
    public function create($channel, $handle);

    /**
     * Create many chat users.
     *
     * @param $channel
     * @param Collection $handles
     */
    public function createMany($channel, Collection $handles);

    /**
     * Update an existing chat user.
     *
     * @param $channel
     * @param $handle
     * @param int $totalMinutesOnline
     * @param int $points
     */
    public function update($channel, $handle, $totalMinutesOnline, $points);

    /**
     * Update many users.
     *
     * @param $channel
     * @param Collection $users
     */
    public function updateMany($channel, Collection $users);

    /**
     * Set a user to offline.
     *
     * @param $channel
     * @param $handle
     * @return mixed
     */
    public function offline($channel, $handle);

    /**
     * Offline many users.
     *
     * @param $channel
     * @param Collection $handles
     */
    public function offlineMany($channel, Collection $handles);

    /**
     * Offline all the users for a channel.
     *
     * @param $channel
     * @return mixed
     */
    public function offlineAllForChannel($channel);

}
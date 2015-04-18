<?php

namespace App\Contracts\Repositories;

use App\User;
use Illuminate\Support\Collection;

interface ChatterRepository {

	/**
     * Get all chatters belonging to a user.
     *
     * @param User $user
     * @return mixed
     */
    public function allForUser(User $user);

    /**
     * Find a single chatter by their handle and which users owns them.
     *
     * @param User $user
     * @param $handle
     * @return array
     */
    public function findByHandle(User $user, $handle);

    /**
     * Create a new chat user.
     *
     * @param User $user
     * @param $handle
     * @return
     */
    public function create(User $user, $handle);

    /**
     * Create many chat users.
     *
     * @param User $user
     * @param Collection $handles
     */
    public function createMany(User $user, Collection $handles);

    /**
     * Update an existing chat user.
     *
     * @param User $user
     * @param $handle
     * @param int $totalMinutesOnline
     * @param int $points
     */
    public function update(User $user, $handle, $totalMinutesOnline, $points);

    /**
     * Update many users.
     *
     * @param User $user
     * @param Collection $users
     */
    public function updateMany(User $user, Collection $users);

    /**
     * Set a user to offline.
     *
     * @param User $user
     * @param $handle
     * @return mixed
     */
    public function offline(User $user, $handle);

    /**
     * Offline many users.
     *
     * @param User $user
     * @param Collection $handles
     */
    public function offlineMany(User $user, Collection $handles);

    /**
     * Offline all the users for a channel.
     *
     * @param User $user
     * @return mixed
     */
    public function offlineAllForChannel(User $user);

	/**
     * Update the rank of a chatter.
     *
     * @param $chatterId
     * @param $rank
     * @return mixed
     */
    public function updateRank($chatterId, $rank);

	/**
     * Update the rank of many chatters.
     *
     * @param Collection $chatters
     * @return mixed
     */
    public function updateRankMany(Collection $chatters);

}
<?php

namespace App\Contracts\Repositories;

use App\Channel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Predis\Pipeline\Pipeline;

interface ChatterRepository
{
    /**
     * Set the giveaway status for a user.
     *
     * @param $status
     * @return mixed
     */
    public function setGiveAwayStatus(Channel $channel, $handle, $status);

    /**
     * Get the time the points system was last updated for a channel.
     *
     * Channel $channel
     * @return string
     */
    public function lastUpdate(Channel $channel);

    /**
     * Set the time the points system for a channel was last updated.
     *
     * @param Channel $channel
     * @param Carbon $time
     *
     * @return mixed
     */
    public function setLastUpdate(Channel $channel, Carbon $time);

    /**
     * Get all chatters belonging to a channel.
     *
     * @param Channel $channel
     * @param boolean $showHidden
     * @param boolean $showMod
     * @return Collection
     */
    public function allForChannel(Channel $channel, $showHidden = false, $showMod = false);

    /**
     * Get all mods belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allModsForChannel(Channel $channel);

    /**
     * Remove a moderator from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeMod(Channel $channel, $handle);

    /**
     * Add a moderator to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addMod(Channel $channel, $handle);

    /**
     * Get all admins belonging to a channel.
     *
     * @param Channel $channel
     * @return Collection
     */
    public function allAdminsForChannel(Channel $channel);

    /**
     * Remove an admin from a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function removeAdmin(Channel $channel, $handle);

    /**
     * Add an administor to a channel.
     *
     * @param Channel $channel
     * @param array $handle
     */
    public function addAdmin(Channel $channel, $handle);

    /**
     * Get the number of chatters a channel has.
     *
     * @param Channel $channel
     * @return int
     */
    public function getCountForChannel(Channel $channel);

    /**
     * Find a single chatter by their handle and which users owns them.
     *
     * @param Channel $channel
     * @param $handle
     * @return array
     */
    public function findByHandle(Channel $channel, $handle);

    /**
     * Delete a chatter, will only delete moderators.
     *
     * @param Channel $channel
     * @param $handle
     *
     * @return bool
     */
    public function deleteChatter(Channel $channel, $handle);

    /**
     * Delete a channel. This only deletes the indexes.
     *
     * @param Channel $channel
     */
    public function deleteChannel(Channel $channel);

    /**
     * Setup pagination for results.
     *
     * @param int $page
     * @param int $limit
     *
     * @return $this
     */
    public function paginate($page = 1, $limit = 100);

    /**
     * Update/Create a chatter.
     *
     * @param Channel $channel
     * @param string|array $handles
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateChatter(Channel $channel, $handle, $minutes = 0, $points = 0);

    /**
     * Update/Create a moderator.
     *
     * @param Channel $channel
     * @param string|array $handles
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateModerator(Channel $channel, $handle, $minutes = 0, $points = 0);
}

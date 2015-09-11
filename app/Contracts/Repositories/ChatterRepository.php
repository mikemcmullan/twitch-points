<?php

namespace App\Contracts\Repositories;

use App\Channel;
use Carbon\Carbon;
use Predis\Pipeline\Pipeline;

interface ChatterRepository {

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
     * @return Collection
     */
    public function allForChannel(Channel $channel);

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
     * @param $handle
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateChatter(Channel $channel, $handle, $minutes = 0, $points = 0, Pipeline $pipe = null);

    /**
     * Update/Create a group of chatters.
     *
     * @param Channel $channel
     * @param array $chatters
     * @param $minutes
     * @param $points
     */
    public function updateChatters(Channel $channel, array $chatters, $minutes = 0, $points = 0);

    /**
     * Update/Create a moderator.
     *
     * @param Channel $channel
     * @param $handle
     * @param int $minutes
     * @param int $points
     * @param Pipeline $pipe
     */
    public function updateModerator(Channel $channel, $handle, $minutes = 0, $points = 0, Pipeline $pipe = null);

    /**
     * Update/Create a group of moderators.
     *
     * @param Channel $channel
     * @param array $chatters
     * @param $minutes
     * @param $points
     */
    public function updateModerators(Channel $channel, array $chatters, $minutes = 0, $points = 0);

    /**
     * Update rankings for chatters.
     *
     * @param Channel $channel
     * @param array $chatters
     */
    public function updateRankings(Channel $channel, array $chatters);

}
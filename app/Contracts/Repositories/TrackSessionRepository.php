<?php

namespace App\Contracts\Repositories;

use App\TrackSession;
use App\Channel;

interface TrackSessionRepository {

	/**
	 * Create a new track session.
	 *
	 * @param Channel $channel
	 *
	 * @return static
	 */
	public function create(Channel $channel);

	/**
	 * Complete a track session.
	 *
	 * @param TrackSession $session
	 *
	 * @return bool|int
	 */
	public function end(TrackSession $session);

	/**
	 * Find uncompleted track sessions for a user.
	 *
	 * @param Channel $channel
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function findUncompletedSession(Channel $channel);

	/**
	 * Find all uncompleted track sessions.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function allUncompletedSessions();

}
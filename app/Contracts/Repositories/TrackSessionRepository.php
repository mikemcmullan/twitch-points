<?php

namespace App\Contracts\Repositories;

use App\TrackSession;
use App\User;

interface TrackSessionRepository {

	/**
	 * Create a new track session.
	 *
	 * @param User $user
	 *
	 * @return static
	 */
	public function create(User $user);

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
	 * @param User $user
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function findUncompletedSession(User $user);

	/**
	 * Find all uncompleted track sessions.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function allUncompletedSessions();

}
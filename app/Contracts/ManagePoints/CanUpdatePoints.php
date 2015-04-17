<?php

namespace App\Contracts\ManagePoints;

use App\User;

interface CanUpdatePoints {

	/**
	 * Get an instance of the chatter repository.
	 *
	 * @return ChatterRepository
	 */
	public function getChatterRepository();

	/**
	 * Add points to a chatter.
	 *
	 * @param User $user
	 * @param $handle
	 * @param $points
	 *
	 * @return mixed
	 */
	public function addPoints(User $user, $handle, $points);

	/**
	 * Remove points from a chatter.
	 *
	 * @param User $user
	 * @param $handle
	 * @param $points
	 *
	 * @return mixed
	 */
	public function removePoints(User $user, $handle, $points);

}
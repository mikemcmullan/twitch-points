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
	 * Get an instance of the user repository.
	 *
	 * @return UserRepository
	 */
	public function getUserRepository();

	/**
	 * Add points to a chatter.
	 *
	 * @param $user
	 * @param $handle
	 * @param $points
	 *
	 * @return mixed
	 */
	public function addPoints($user, $handle, $points);

	/**
	 * Remove points from a chatter.
	 *
	 * @param $user
	 * @param $handle
	 * @param $points
	 *
	 * @return mixed
	 */
	public function removePoints($user, $handle, $points);

}
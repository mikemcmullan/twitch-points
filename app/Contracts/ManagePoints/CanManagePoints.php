<?php

namespace App\Contracts\ManagePoints;

interface CanManagePoints {

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
	public function getChannelRepository();

	/**
	 * Add points to a chatter.
	 *
	 * @param $user
	 * @param $handle
	 * @param $target
	 * @param $points
	 *
	 * @return mixed
	 */
	public function addPoints($user, $handle, $target, $points);

	/**
	 * Remove points from a chatter.
	 *
	 * @param $user
	 * @param $handle
	 * @param $target
	 * @param $points
	 *
	 * @return mixed
	 */
	public function removePoints($user, $handle, $target, $points);

	/**
	 * Get points for a chatter.
	 *
	 * @param $user
	 * @param $handle
	 *
	 * @return mixed
	 */
	public function getPoints($user, $handle);

}
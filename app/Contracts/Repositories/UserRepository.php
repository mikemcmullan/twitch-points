<?php

namespace App\Contracts\Repositories;

use App\Channel;
use App\User;

interface UserRepository {

	/**
	 * Find a user by their name.
	 *
	 * @param Channel $channel
	 * @param $name
	 *
	 * @return mixed
	 */
	public function findByName(Channel $channel, $name);

	/**
	 * Update a user.
	 *
	 * @param User $user
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function update(User $user, array $data = []);
}
<?php

namespace App\Repositories\Users;

use App\User;

interface UserRepository {

	/**
	 * Find a user by their name or create a new user.
	 *
	 * @param $name
	 * @param array $data
	 * @return static
	 */
	public function findByNameOrCreate($name, array $data = []);

	/**
	 * Find a user by their name.
	 *
	 * @param $name
	 * @return mixed
	 */
	public function findByName($name);

	/**
	 * Update a user.
	 *
	 * @param User $user
	 * @return bool
	 */
	public function update(User $user);

}
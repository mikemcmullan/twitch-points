<?php

namespace App\Contracts\Repositories;

use App\Channel;

interface ChannelRepository {

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
	 * @param Channel $channel
	 * @return bool
	 */
	public function update(Channel $channel);

}
<?php

namespace App\Repositories\User;

use App\Channel;
use App\Contracts\Repositories\UserRepository;
use App\User;

class EloquentUserRepository implements UserRepository {

	/**
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Find a user by their name.
	 *
	 * @param Channel $channel
	 * @param $name
	 *
	 * @return mixed
	 */
	public function findByName(Channel $channel, $name)
	{
		return $this->user->whereHas('channels', function ($query) use ($channel) {
				$query->where('slug', $channel->slug);
			})->where('name', $name)->first();
	}

	/**
	 * Update a user.
	 *
	 * @param User $user
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function update(User $user, array $data = [])
	{
		return $user->update($data);
	}
}
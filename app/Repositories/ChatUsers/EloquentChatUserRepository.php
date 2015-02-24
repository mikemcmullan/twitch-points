<?php

namespace App\Repositories\ChatUsers;

use App\ChatUser;
use App\User;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

class EloquentChatUserRepository {

	/**
	 * @var DatabaseManager
	 */
	private $db;

	/**
	 * @var Repository
	 */
	private $config;

	/**
	 * @var ChatUser
	 */
	private $chatUser;

	/**
	 * @param ChatUser $chatUser
	 * @param DatabaseManager $db
	 * @param Repository $config
	 */
	public function __construct(ChatUser $chatUser, DatabaseManager $db, Repository $config)
	{
		$this->db = $db;
		$this->config = $config;
		$this->chatUser = $chatUser;
	}

	/**
	 * Get all chatUsers that belong to a User.
	 *
	 * @param User $user
	 * @param null $limit
	 *
	 * @return mixed
	 */
	public function allForUser(User $user, $limit = null)
	{
		$query = $this->chatUser
			->where('user_id', $user['id'])
			->orderBy('points', 'desc');

		if ($limit > 0)
		{
			$query->limit(25);
		}

		return $query->get();
	}

	/**
	 * Find a chatUser by their handle.
	 *
	 * @param User $user
	 * @param $handle
	 *
	 * @return mixed
	 */
	public function findByHandle(User $user, $handle)
	{
		return $this->chatUser
			->where('user_id', $user['id'])
			->where('handle', $handle)
			->first();
	}
}
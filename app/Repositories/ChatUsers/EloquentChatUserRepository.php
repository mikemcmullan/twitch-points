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

	public function __construct(ChatUser $chatUser, DatabaseManager $db, Repository $config)
	{
		$this->db = $db;
		$this->config = $config;
		$this->chatUser = $chatUser;
	}

	public function allForUser(User $user)
	{
		return $this->chatUser
			->where('user_id', $user['id'])
			->orderBy('points', 'desc')
			->get();
	}
}
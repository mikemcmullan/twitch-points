<?php

namespace App\Repositories\Chatters;

use App\Chatter;
use App\User;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

class EloquentChatterRepository {

	/**
	 * @var DatabaseManager
	 */
	private $db;

	/**
	 * @var Repository
	 */
	private $config;

	/**
	 * @var Chatter
	 */
	private $chatter;

	/**
	 * @var Collection
	 */
	private $hiddenChatters;

	/**
	 * @param Chatter $chatter
	 * @param DatabaseManager $db
	 * @param Repository $config
	 */
	public function __construct(Chatter $chatter, DatabaseManager $db, Repository $config)
	{
		$this->db = $db;
		$this->config = $config;
		$this->chatter = $chatter;
		$this->hiddenChatters = new Collection;
	}

	/**
	 * @return Collection
	 */
	public function getHiddenChatters()
	{
		return $this->hiddenChatters;
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
		$query = $this->chatter
			->where('user_id', $user['id'])
			->orderBy('points', 'desc')
			->whereNotIn('handle', $this->hiddenChatters->all());

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
		return $this->chatter
			->where('user_id', $user['id'])
			->where('handle', $handle)
			->first();
	}
}
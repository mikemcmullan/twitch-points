<?php

namespace App\Services;

use App\Repositories\Chatters\EloquentChatterRepository;
use App\User;
use Illuminate\Support\Collection;

class RankChatters {

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var EloquentChatterRepository
	 */
	private $chatterRepository;

	/**
	 * @param User $user
	 * @param EloquentChatterRepository $chatterRepository
	 */
	public function __construct(User $user, EloquentChatterRepository $chatterRepository)
	{
		$this->user = $user;
		$this->chatterRepository = $chatterRepository;
	}

	public function rank()
	{
		$chatters = $this->chatterRepository->allForUser($this->user);
		$rankings = new Collection();
		$rank = 1;

		foreach ($chatters->groupBy('points') as $group)
		{
			foreach ($group as $chatter)
			{
				$chatter['rank'] = $rank;

				$rankings->push($chatter);
			}

			$rank++;
		}

		$this->chatterRepository->updateRankings($rankings);
	}
}
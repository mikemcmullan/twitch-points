<?php

namespace App\Services;

use App\Repositories\Chatters\ChatterRepository;
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
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(User $user, ChatterRepository $chatterRepository)
	{
		$this->user = $user;
		$this->chatterRepository = $chatterRepository;
	}

	/**
	 * Run the ranking.
	 */
	public function rank()
	{
		// Should replace in future.
		$chatters = app(EloquentChatterRepository::class)->allForUser($this->user);

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

		$this->chatterRepository->updateRankMany($rankings);
	}
}
<?php namespace App\Handlers\Events;

use App\Contracts\Repositories\ChatterRepository;
use App\Events\ChatListWasDownloaded;
use Illuminate\Database\Eloquent\Collection;

class RankChatters {

	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * Create the event handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(ChatterRepository $chatterRepository)
	{
		$this->chatterRepository = $chatterRepository;
	}

	/**
	 * Handle the event.
	 *
	 * @param  ChatListWasDownloaded  $event
	 * @return void
	 */
	public function handle(ChatListWasDownloaded $event)
	{
		$chatters = $this->chatterRepository->allForChannel($event->channel);
		$rankings = new Collection();
		$rank = 1;

		$groups = $chatters->filter(function ($chatter) {
			return ! (array_get($chatter, 'mod') === true || array_get($chatter, 'hide') === true);
		})->groupBy('points');

		foreach ($groups as $group) {
			foreach ($group as $chatter) {
				$chatter['rank'] = $rank;
				$rankings->push($chatter);
			}

			$rank++;
		}

		$this->chatterRepository->updateRankings($event->channel, $rankings->toArray());
	}

}

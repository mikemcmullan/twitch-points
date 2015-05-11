<?php namespace App\Handlers\Events;

use App\Contracts\Repositories\ChatterRepository;
use App\Events\ChatListWasDownloaded;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Carbon\Carbon;

class ProcessChatList {
	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * Create the event handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 * @param ConfigRepository $config
	 */
	public function __construct(ChatterRepository $chatterRepository, ConfigRepository $config)
	{
		$this->chatterRepository = $chatterRepository;
		$this->config = $config;
	}

	/**
	 * Calculate how many minutes have gone by since the system
	 * last run.
	 *
	 * @return int
	 */
	private function calculateMinutes()
	{
		$lastUpdate = $this->chatterRepository->lastUpdate();
		$this->chatterRepository->setLastUpdate(Carbon::now());

		if ($lastUpdate instanceof Carbon)
		{
			return Carbon::now()->diffInMinutes($lastUpdate);
		}

		return 0;
	}

	/**
	 * Calculate how many points the user has earned based
	 * upon how many minutes they've been online.
	 *
	 * @param $minutes
	 * @param $status
	 *
	 * @return float
	 */
	private function calculatePoints($minutes, $status)
	{
		$status = $status === true ? 'online' : 'offline';

		$pointInterval = $this->config->get("twitch.points.{$status}.interval");
		$pointsAwarded = $this->config->get("twitch.points.{$status}.awarded");

		$pointsPerMinute = $pointsAwarded / $pointInterval;

		return round($pointsPerMinute * $minutes, 3);
	}

	/**
	 * Handle the event.
	 *
	 * @param  ChatListWasDownloaded  $event
	 * @return void
	 */
	public function handle(ChatListWasDownloaded $event)
	{
		$minutes = $this->calculateMinutes();
		$points = $this->calculatePoints($minutes, $event->status);

		$chattersList = $event->chatList['chatters'];
		$modList      = $event->chatList['moderators'];

		$this->chatterRepository->updateChatters($event->channel, $chattersList, $minutes, $points);
		$this->chatterRepository->updateModerators($event->channel, $modList, $minutes, $points);

//		$this->chatterRepository->updateMany($event->channel, $chattersList, $minutes, $points);
	}

}

<?php namespace App\Handlers\Commands;

use App\Commands\UpdatePointsCommand;
use App\Exceptions\InvalidChannelException;
use App\Repositories\Chatters\ChatterRepository;
use App\Services\DownloadChatList;
use App\Services\RankChatters;
use App\Services\SortChatters;
use App\Services\TwitchApi;
use App\Services\UpdateDBChatters;
use App\User;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Collection;

class UpdatePointsCommandHandler {

	/**
	 * @var Log
	 */
	private $log;

	/**
	 * @var TwitchApi
	 */
	private $twitchApi;

	/**
	 * Create the command handler.
	 *
	 * @param TwitchApi $twitchApi
	 * @param Log $log
	 */
	public function __construct(TwitchApi $twitchApi, Log $log)
	{
		$this->log = $log;
		$this->twitchApi = $twitchApi;
	}

	/**
	 * Download the chat list for a channel.
	 * @param $channel
	 * @return Collection
     */
	private function downloadChatList($channel)
	{
		$this->log->info('Downloading chat list for ' . $channel, [__METHOD__]);

		return $this->twitchApi->chatList($channel);
	}

	/**
	 * Sort the chatters into online, new online and offline chatters.
	 *
	 * @param User $user
	 * @param Collection $liveChatters
	 * @param ChatterRepository $chatterRepository
	 *
	 * @return SortChatters
	 */
	private function sortChatters(User $user, Collection $liveChatters, ChatterRepository $chatterRepository)
	{
		$this->log->info('Sorting Chat Users for ' . $user['name'], [__METHOD__]);

		return new SortChatters($liveChatters, $chatterRepository->allForUser($user));
	}

	/**
	 * Update the DB with new users, online users and offline users.
	 *
	 * @param User $user
	 * @param SortChatters $sorter
	 * @param ChatterRepository $chatterRepository
	 * @param $channelStatus
	 */
	private function updateDB(User $user, SortChatters $sorter, ChatterRepository $chatterRepository, $channelStatus)
	{
		$this->log->info('Updating DB Chat Users for ' . $user['name'], [__METHOD__]);

		$updater = new UpdateDBChatters(
			$user,
			app('Illuminate\Contracts\Config\Repository'),
			$chatterRepository,
			$channelStatus
		);

		$updater->newChatters($sorter->newChatters());
		$updater->onlineChatters($sorter->onlineChatters());
		$updater->offlineChatters($sorter->offlineChatters());

		(new RankChatters($user, $chatterRepository))->rank();
	}

	/**
	 * Handle the command.
	 *
	 * @param  UpdatePointsCommand $command
	 * @throws InvalidChannelException
	 */
	public function handle(UpdatePointsCommand $command)
	{
		// Check if the channel actually exists.
		if ( ! $this->twitchApi->validChannel($command->user['name']))
		{
			$this->log->info('Invalid channel: "' . $command->user['name'] . '"', [__METHOD__]);

			throw new InvalidChannelException($command->user['name']);
		}

		// Initially set the channel to be online.
		$channelStatus = true;

		// Check if the chanel is online.
		if ( ! $this->twitchApi->channelOnline($command->user['name']))
		{
			$this->log->info('"' . $command->user['name'] . '" is offline.', [__METHOD__]);

			$channelStatus = false;
		}

		$liveChatList 	= $this->downloadChatList($command->user['name']);
		$sorter 		= $this->sortChatters($command->user, $liveChatList, $command->chatterRepository);

		$this->updateDB($command->user, $sorter, $command->chatterRepository, $channelStatus);
	}

}

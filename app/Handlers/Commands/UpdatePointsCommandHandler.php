<?php namespace App\Handlers\Commands;

use App\Commands\UpdatePointsCommand;
use App\Exceptions\InvalidChannelException;
use App\Exceptions\StreamOfflineException;
use App\Repositories\ChatUsers\ChatUserRepository;
use App\Services\DownloadChatList;
use App\Services\SortChatUsers;
use App\Services\TwitchApi;
use App\Services\UpdateDBChatUsers;
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

//		$results = \Cache::remember('chatList_' . $channelName, 10, function() use($channelName)
//		{
//			$list = $this->twitchApi->chatList($channelName);
//			return $list;
//		});

		return $this->twitchApi->chatList($channel);
	}

	/**
	 * Sort the users into online users, new online users and offline users.
	 *
	 * @param User $user
	 * @param Collection $liveChatUsers
	 * @param ChatUserRepository $chatUserRepository
	 * @return SortChatUsers
	 */
	private function sortChatUsers(User $user, Collection $liveChatUsers, ChatUserRepository $chatUserRepository)
	{
		$this->log->info('Sorting Chat Users for ' . $user['name'], [__METHOD__]);

		return new SortChatUsers($liveChatUsers, $chatUserRepository->users($user));
	}

	/**
	 * Update the DB with new users, online users and offline users.
	 *
	 * @param User $user
	 * @param SortChatUsers $sorter
	 * @param ChatUserRepository $chatUserRepository
	 */
	private function updateDB(User $user, SortChatUsers $sorter, ChatUserRepository $chatUserRepository)
	{
		$this->log->info('Updating DB Chat Users for ' . $user['name'], [__METHOD__]);

		$updater = new UpdateDBChatUsers(
			$user,
			app('Illuminate\Contracts\Config\Repository'),
			$chatUserRepository
		);

		$updater->newOnlineUsers($sorter->newOnlineUsers());
		$updater->onlineUsers($sorter->onlineUsers());
		$updater->offlineUsers($sorter->offlineUsers());
	}

	/**
	 * Set all users of a channel to offline.
	 *
	 * @param User $user
	 * @param ChatUserRepository $chatUserRepository
	 *
	 * @return bool
	 */
	private function setAllUsersOffline(User $user, ChatUserRepository $chatUserRepository)
	{
		$updater = new UpdateDBChatUsers(
			$user,
			app('Illuminate\Contracts\Config\Repository'),
			$chatUserRepository
		);

		$updater->setAllUsersOffline($chatUserRepository->users($user));

		return true;
	}

	/**
	 * Handle the command.
	 *
	 * @param  UpdatePointsCommand $command
	 * @return StreamOfflineException
	 * @throws InvalidChannelException
	 */
	public function handle(UpdatePointsCommand $command)
	{
		// Check if the channel actually exists.
		if ( ! $this->twitchApi->validChannel($command->user['name']))
		{
			$this->log->info('Invalid channel: "' . $command->user['name'] . '"', [__METHOD__]);

			return new InvalidChannelException($command->user['name']);
		}

		// Check if the chanel is online.
		if ( ! $this->twitchApi->channelOnline($command->user['name']))
		{
			$this->log->info('"' . $command->user['name'] . '" is offline.', [__METHOD__]);

			$this->setAllUsersOffline($command->user, $command->chatUserRepository);

			return new StreamOfflineException($command->user['name']);
		}

		$liveChatList 	= $this->downloadChatList($command->user['name']);
		$sorter 		= $this->sortChatUsers($command->user, $liveChatList, $command->chatUserRepository);

		$this->updateDB($command->user, $sorter, $command->chatUserRepository);
	}

}

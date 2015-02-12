<?php namespace App\Handlers\Commands;

use App\ChatUser;
use App\ChatUserCollection;
use App\Commands\UpdatePoints;

use App\Exceptions\InvalidChannelException;
use App\Exceptions\StreamOfflineException;
use App\Repositories\ChatUsers\ChatUserRepository;
use App\Services\DownloadChatList;
use App\Services\SortChatUsers;
use App\Services\TwitchApi;
use App\Services\UpdateDBChatUsers;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Collection;

class UpdatePointsHandler {

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
	 * @param $channel
	 * @param Collection $liveChatUsers
	 * @param ChatUserRepository $chatUserRepository
	 * @return SortChatUsers
	 */
	private function sortChatUsers($channel, Collection $liveChatUsers, ChatUserRepository $chatUserRepository)
	{
		$this->log->info('Sorting Chat Users for ' . $channel, [__METHOD__]);

		return new SortChatUsers($liveChatUsers, $chatUserRepository->users($channel));
	}

	/**
	 * Update the DB with new users, online users and offline users.
	 *
	 * @param $channel
	 * @param SortChatUsers $sorter
	 * @param ChatUserRepository $chatUserRepository
	 */
	private function updateDB($channel, SortChatUsers $sorter, ChatUserRepository $chatUserRepository)
	{
		$this->log->info('Updating DB Chat Users for ' . $channel, [__METHOD__]);

		$updater = new UpdateDBChatUsers(
			$channel,
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
	 * @param $channel
	 * @param ChatUserRepository $chatUserRepository
	 * @return bool
	 */
	private function setAllUsersOffline($channel, ChatUserRepository $chatUserRepository)
	{
		$updater = new UpdateDBChatUsers(
			$channel,
			app('Illuminate\Contracts\Config\Repository'),
			$chatUserRepository
		);

		$updater->setAllUsersOffline($chatUserRepository->users($channel));

		return true;
	}

	/**
	 * Handle the command.
	 *
	 * @param  UpdatePoints $command
	 * @return StreamOfflineException
	 * @throws InvalidChannelException
	 */
	public function handle(UpdatePoints $command)
	{
		// Check if the channel actually exists.
		if ( ! $this->twitchApi->validChannel($command->channel))
		{
			throw new InvalidChannelException($command->channel);
		}

		// Check if the chanel is online.
		if ( ! $this->twitchApi->channelOnline($command->channel))
		{
			$this->setAllUsersOffline($command->channel, $command->chatUserRepository);

			return new StreamOfflineException($command->channel);
		}

		$liveChatList 	= $this->downloadChatList($command->channel);
		$sorter 		= $this->sortChatUsers($command->channel, $liveChatList, $command->chatUserRepository);

		$this->updateDB($command->channel, $sorter, $command->chatUserRepository);
	}

}

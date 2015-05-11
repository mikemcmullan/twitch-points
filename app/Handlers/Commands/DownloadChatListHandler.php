<?php namespace App\Handlers\Commands;

use App\Commands\DownloadChatList;

use App\Events\ChatListWasDownloaded;
use App\Exceptions\InvalidChannelException;
use App\Services\TwitchApi;
use Illuminate\Events\Dispatcher;

class DownloadChatListHandler {

	/**
	 * @var TwitchApi
	 */
	private $twitchApi;

	/**
	 * @var Dispatcher
	 */
	private $events;

	/**
	 * Create the command handler.
	 *
	 * @param TwitchApi $twitchApi
	 * @param Dispatcher $events
	 */
	public function __construct(TwitchApi $twitchApi, Dispatcher $events)
	{
		$this->twitchApi = $twitchApi;
		$this->events = $events;
	}

	/**
	 * Handle the command.
	 *
	 * @param  DownloadChatList $command
	 *
	 * @return \Illuminate\Support\Collection
	 * @throws InvalidChannelException
	 */
	public function handle(DownloadChatList $command)
	{
		if ( ! $this->twitchApi->validChannel($command->channel['name']))
		{
			throw new InvalidChannelException($command->channel['name']);
		}

		$status     = $this->twitchApi->channelOnline($command->channel['name']);
		$chatList   = $this->twitchApi->chatList($command->channel['name']);

		$this->events->fire(new ChatListWasDownloaded($command->channel, $chatList, $status));

		return $chatList;
	}

}

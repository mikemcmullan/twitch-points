<?php

namespace App\Listeners;

use App\Events\ChatListWasDownloaded;
use App\Services\TwitchApi;
use Redis;

class RetrieveUserInfo
{
	public function __construct(TwitchAPi $twitchApi)
	{
		$this->twitchApi = $twitchApi;
	}

	/**
     * Handle the event.
     *
     * @param  ChatListWasDownloaded  $event
     * @return void
     */
	public function handle(ChatListWasDownloaded $event)
	{
		$toFetch = [];

		// Go through the chat list and get the users info from the cache, if it
		// is not there remove them from the chat list and add them to the
		// $toFetch list.
		foreach ($event->chatList as $typeKey => $type) {
			foreach ($type as $userListKey => $userList) {
				$event->chatList[$typeKey][$userListKey] = array_map(function ($chatter) {
					return getUserFromRedis($chatter) ?? $chatter;
				}, $event->chatList[$typeKey][$userListKey]);

				$event->chatList[$typeKey][$userListKey] = array_filter($event->chatList[$typeKey][$userListKey], function ($chatter) use (&$toFetch) {
					$bool = is_array($chatter);

					if (!$bool) {
						$toFetch[] = $chatter;
					}

					return $bool;
				});
			}
		}

		foreach ($toFetch as $username) {
			Redis::sadd('twitch:toFetch', $username);
		}
	}
}

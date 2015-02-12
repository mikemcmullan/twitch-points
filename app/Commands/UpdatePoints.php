<?php namespace App\Commands;

use App\ChatUserCollection;
use App\Commands\Command;

use App\Repositories\ChatUsers\ChatUserRepository;
use App\Services\DBImportChatUsers;

class UpdatePoints extends Command {

	/**
	 * @var
	 */
	public $channel;

	/**
	 * @var ChatUser
	 */
	public $chatUserRepository;

	/**
	 * Create a new command instance.
	 *
	 * @param $channel
	 * @param ChatUserRepository $chatUserRepository
	 */
	public function __construct($channel, ChatUserRepository $chatUserRepository)
	{
		$this->channel = $channel;
		$this->chatUserRepository = $chatUserRepository;
	}
}

<?php namespace App\Commands;

use App\ChatUserCollection;
use App\Commands\Command;

use App\Repositories\ChatUsers\ChatUserRepository;
use App\Services\DBImportChatUsers;
use App\User;

class UpdatePointsCommand extends Command {

	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var ChatUser
	 */
	public $chatUserRepository;

	/**
	 * Create a new command instance.
	 *
	 * @param User $user
	 * @param ChatUserRepository $chatUserRepository
	 */
	public function __construct(User $user, ChatUserRepository $chatUserRepository)
	{
		$this->user = $user;
		$this->chatUserRepository = $chatUserRepository;
	}
}

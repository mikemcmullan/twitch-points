<?php namespace App\Commands;

use App\ChatUserCollection;
use App\Commands\Command;

use App\Repositories\Chatters\ChatterRepository;
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
	public $chatterRepository;

	/**
	 * Create a new command instance.
	 *
	 * @param User $user
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(User $user, ChatterRepository $chatterRepository)
	{
		$this->user = $user;
		$this->chatterRepository = $chatterRepository;
	}
}

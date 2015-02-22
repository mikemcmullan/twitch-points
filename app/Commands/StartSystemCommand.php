<?php namespace App\Commands;

use App\Commands\Command;

use App\User;

class StartSystemCommand extends Command {

	/**
	 * @var User
	 */
	public $user;

	/**
	 * Create a new command instance.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}
}

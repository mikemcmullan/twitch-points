<?php namespace App\Commands;

use App\Commands\Command;
use App\User;

class AddPointsCommand extends Command {

	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var
	 */
	public $handle;

	/**
	 * @var
	 */
	public $points;

	/**
	 * Create a new command instance.
	 *
	 * @param User $user
	 * @param $handle
	 * @param $points
	 */
	public function __construct(User $user, $handle, $points)
	{
		$this->user = $user;
		$this->handle = $handle;
		$this->points = $points;
	}

}

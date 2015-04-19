<?php namespace App\Commands;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddPointsCommand extends Command {

	use InteractsWithQueue, SerializesModels;

	/**
	 * @var
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
	 * @param $user
	 * @param $handle
	 * @param $points
	 */
	public function __construct($user, $handle, $points)
	{
		$this->user = $user;
		$this->handle = $handle;
		$this->points = $points;
	}

}

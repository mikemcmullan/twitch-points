<?php namespace App\Commands;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddPointsCommand extends Command {

	use InteractsWithQueue, SerializesModels;

	/**
	 * @var
	 */
	public $channel;

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
	 * @param $channel
	 * @param $handle
	 * @param $points
	 */
	public function __construct($channel, $handle, $points)
	{
		$this->channel = $channel;
		$this->handle = $handle;
		$this->points = $points;
	}

}

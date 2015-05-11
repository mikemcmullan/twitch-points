<?php namespace App\Commands;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetPointsCommand extends Command {

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
	 * Create a new command instance.
	 *
	 * @param $channel
	 * @param $handle
	 */
	public function __construct($channel, $handle)
	{
		$this->channel = $channel;
		$this->handle = $handle;
	}

}

<?php namespace App\Commands;

use App\Channel;
use App\Commands\Command;

class DownloadChatList extends Command {

	/**
	 * @var
	 */
	public $channel;

	/**
	 * Create a new command instance.
	 *
	 * @param $channel
	 */
	public function __construct(Channel $channel)
	{
		$this->channel = $channel;
	}

}

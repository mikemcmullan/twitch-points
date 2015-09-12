<?php namespace App\Handlers\Events;

use App\Contracts\Repositories\ChatterRepository;
use App\Events\ChatListWasDownloaded;
use App\Commands\RankChattersCommand;
use Illuminate\Foundation\Bus\DispatchesCommands;

class RankChatters {

	use DispatchesCommands;

	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * Create the event handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(ChatterRepository $chatterRepository)
	{
		$this->chatterRepository = $chatterRepository;
	}

	/**
	 * Handle the event.
	 *
	 * @param  ChatListWasDownloaded  $event
	 * @return void
	 */
	public function handle(ChatListWasDownloaded $event)
	{
		$this->dispatch(new RankChattersCommand($channel));
	}

}

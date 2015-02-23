<?php namespace App\Handlers\Commands;

use App\Commands\StartSystemCommand;

use App\Repositories\ChatUsers\ChatUserRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;

class StartSystemCommandHandler {

	/**
	 * @var TrackSessionRepository
	 */
	private $trackSessionRepository;

	/**
	 * @var ChatUserRepository
	 */
	private $chatUserRepository;

	/**
	 * Create the command handler.
	 *
	 * @param TrackSessionRepository $trackSessionRepository
	 * @param ChatUserRepository $chatUserRepository
	 */
	public function __construct(TrackSessionRepository $trackSessionRepository, ChatUserRepository $chatUserRepository)
	{
		$this->trackSessionRepository = $trackSessionRepository;
		$this->chatUserRepository = $chatUserRepository;
	}

	/**
	 * Handle the command.
	 *
	 * @param  StartSystemCommand $command
	 * @return \App\User
	 */
	public function handle(StartSystemCommand $command)
	{
		$session = $this->trackSessionRepository->findUncompletedSession($command->user);

		if ( ! $session)
		{
			return $this->trackSessionRepository->create($command->user);
		}

		$this->chatUserRepository->offlineAllForChannel($command->user);

		return $this->trackSessionRepository->end($session);
	}

}

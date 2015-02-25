<?php namespace App\Handlers\Commands;

use App\Commands\StartSystemCommand;

use App\Repositories\Chatters\ChatterRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;

class StartSystemCommandHandler {

	/**
	 * @var TrackSessionRepository
	 */
	private $trackSessionRepository;

	/**
	 * @var ChatUserRepository
	 */
	private $chatterRepository;

	/**
	 * Create the command handler.
	 *
	 * @param TrackSessionRepository $trackSessionRepository
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(TrackSessionRepository $trackSessionRepository, ChatterRepository $chatterRepository)
	{
		$this->trackSessionRepository = $trackSessionRepository;
		$this->chatterRepository = $chatterRepository;
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

		$this->chatterRepository->offlineAllForChannel($command->user);

		return $this->trackSessionRepository->end($session);
	}

}

<?php namespace App\Handlers\Commands;

use App\Commands\StartSystemCommand;

use App\Contracts\Repositories\ChatterRepository;
use App\Contracts\Repositories\TrackSessionRepository;
use Carbon\Carbon;

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
	 * @return \App\Channel
	 */
	public function handle(StartSystemCommand $command)
	{
		$session = $this->trackSessionRepository->findUncompletedSession($command->channel);

		if ( ! $session)
		{
			$this->chatterRepository->setLastUpdate($command->channel, Carbon::now());

			return $this->trackSessionRepository->create($command->channel);
		}

		return $this->trackSessionRepository->end($session);
	}

}

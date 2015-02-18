<?php namespace App\Handlers\Commands;

use App\Commands\StartSystemCommand;

use App\Repositories\Users\UserRepository;

class StartSystemCommandHandler {

	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * Create the command handler.
	 * @param UserRepository $userRepository
	 */
	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * Handle the command.
	 *
	 * @param  StartSystemCommand $command
	 * @return \App\User
	 */
	public function handle(StartSystemCommand $command)
	{
		if ($command->user['trackPoints']->isEmpty())
		{
			return $this->userRepository->createTrackPointsSession($command->user);
		}

		return $this->userRepository->endTrackPointsSession($command->user);
	}

}

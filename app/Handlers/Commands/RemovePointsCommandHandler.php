<?php namespace App\Handlers\Commands;

use App\Commands\RemovePointsCommand;
use App\Contracts\ManagePoints\CanUpdatePoints;
use App\Contracts\Repositories\UserRepository;
use App\ManagePoints\UpdatePointsTrait;
use App\Contracts\Repositories\ChatterRepository;

class RemovePointsCommandHandler implements CanUpdatePoints {

	use UpdatePointsTrait;

	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * Create the command handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 * @param UserRepository $userRepository
	 */
	public function __construct(ChatterRepository $chatterRepository, UserRepository $userRepository)
	{
		$this->chatterRepository = $chatterRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * Get an instance of the chatter repository.
	 *
	 * @return ChatterRepository
	 */
	public function getChatterRepository()
	{
		return $this->chatterRepository;
	}

	/**
	 * Get an instance of the user repository.
	 *
	 * @return ChatterRepository
	 */
	public function getUserRepository()
	{
		return $this->userRepository;
	}

	/**
	 * Handle the command.
	 *
	 * @param  RemovePointsCommand  $command
	 * @return void
	 */
	public function handle(RemovePointsCommand $command)
	{
		return $this->removePoints($command->user, $command->handle, $command->points);
	}

}

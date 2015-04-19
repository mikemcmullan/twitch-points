<?php namespace App\Handlers\Commands;

use App\Commands\AddPointsCommand;
use App\Contracts\ManagePoints\CanUpdatePoints;
use App\Contracts\Repositories\UserRepository;
use App\ManagePoints\UpdatePointsTrait;
use App\Contracts\Repositories\ChatterRepository;

class AddPointsCommandHandler implements CanUpdatePoints {

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
	 * @param  AddPointsCommand  $command
	 * @return void
	 */
	public function handle(AddPointsCommand $command)
	{
		return $this->addPoints($command->user, $command->handle, $command->points);
	}

}

<?php namespace App\Handlers\Commands;

use App\Commands\AddPointsCommand;
use App\Contracts\ManagePoints\CanUpdatePoints;
use App\ManagePoints\UpdatePointsTrait;
use App\Repositories\Chatters\ChatterRepository;
use Illuminate\Queue\InteractsWithQueue;

class AddPointsCommandHandler implements CanUpdatePoints {

	use UpdatePointsTrait;

	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * Create the command handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 */
	public function __construct(ChatterRepository $chatterRepository)
	{
		$this->chatterRepository = $chatterRepository;
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

<?php namespace App\Handlers\Commands;

use App\Commands\RemovePointsCommand;
use App\Contracts\ManagePoints\CanUpdatePoints;
use App\ManagePoints\UpdatePointsTrait;
use App\Repositories\Chatters\ChatterRepository;
use Illuminate\Queue\InteractsWithQueue;

class RemovePointsCommandHandler implements CanUpdatePoints {

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
	 * @param  RemovePointsCommand  $command
	 * @return void
	 */
	public function handle(RemovePointsCommand $command)
	{
		return $this->removePoints($command->user, $command->handle, $command->points);
	}

}

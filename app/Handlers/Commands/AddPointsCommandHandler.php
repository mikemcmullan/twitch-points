<?php namespace App\Handlers\Commands;

use App\Commands\AddPointsCommand;
use App\Contracts\ManagePoints\CanManagePoints;
use App\Contracts\Repositories\ChannelRepository;
use App\ManagePoints\ManagePointsTrait;
use App\Contracts\Repositories\ChatterRepository;

class AddPointsCommandHandler implements CanManagePoints {

	use ManagePointsTrait;

	/**
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * @var ChannelRepository
	 */
	private $channelRepository;

	/**
	 * Create the command handler.
	 *
	 * @param ChatterRepository $chatterRepository
	 * @param ChannelRepository $channelRepository
	 */
	public function __construct(ChatterRepository $chatterRepository, ChannelRepository $channelRepository)
	{
		$this->chatterRepository = $chatterRepository;
		$this->channelRepository = $channelRepository;
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
	public function getChannelRepository()
	{
		return $this->channelRepository;
	}

	/**
	 * Handle the command.
	 *
	 * @param  AddPointsCommand  $command
	 * @return void
	 */
	public function handle(AddPointsCommand $command)
	{
		$this->validate($command);

		return $this->addPoints($command->channel, $command->handle, $command->target, $command->points);
	}

}

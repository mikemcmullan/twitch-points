<?php namespace App\Handlers\Commands;

use App\Commands\GetViewerCommand;
use App\Contracts\ManagePoints\CanManagePoints;
use App\Contracts\Repositories\ChannelRepository;
use App\ManagePoints\ManagePointsTrait;
use App\Contracts\Repositories\ChatterRepository;
use InvalidArgumentException;

class GetViewerCommandHandler implements CanManagePoints {

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
	 * @return ChannelRepository
	 */
	public function getChannelRepository()
	{
		return $this->channelRepository;
	}

	/**
	 * Handle the command.
	 *
	 * @param  GetViewerCommand $command
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function handle(GetViewerCommand $command)
	{
		if ($command->handle === null)
		{
			throw new InvalidArgumentException('handle is a required parameter.');
		}

		$viewer = $this->getPoints($command->channel, $command->handle);

		return [
			'channel'   => $viewer['channel']['name'],
			'handle'    => $viewer['handle'],
			'points'    => floor($viewer['points']),
			'minutes'   => (int) $viewer['minutes'],
			'rank'      => (int) array_get($viewer, 'rank'),
			'mod'       => (bool) array_get($viewer, 'mod')
		];
	}

}

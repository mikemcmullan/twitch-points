<?php

namespace App\Handlers\Commands;

use App\Commands\RemovePointsCommand;
use App\Contracts\ManagePoints\CanManagePoints;
use App\Contracts\Repositories\ChannelRepository;
use App\ManagePoints\ManagePointsTrait;
use App\Contracts\Repositories\ChatterRepository;

class RemovePointsCommandHandler implements CanManagePoints
{
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
     * @param  RemovePointsCommand  $command
     * @return void
     */
    public function handle(RemovePointsCommand $command)
    {
        $this->validate($command);

        return $this->removePoints($command->channel, $command->handle, $command->target, $command->points);
    }
}

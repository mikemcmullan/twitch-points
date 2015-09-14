<?php

namespace App\Console\Commands;

use App\Commands\RemoveOldViewersCommand;
use App\Contracts\Repositories\ChannelRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemoveOldViewers extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'points:remove-old-viewers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old inactive viewers from the DB.';

    /**
     * @var Repository
     */
    private $config;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * Create a new command instance.
     *
     * @param Repository $config
     * @param ChannelRepository $channelRepository
     */
    public function __construct(Repository $config, ChannelRepository $channelRepository)
    {
        parent::__construct();
        $this->config = $config;
        $this->channelRepository = $channelRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $channel = $this->channelRepository->findByName($this->config->get('twitch.points.default_channel'));
        $minutes = $this->config->get('twitch.points.expire.days');
        $points  = $this->config->get('twitch.points.expire.points');

        $this->dispatch(new RemoveOldViewersCommand($channel, $minutes, $points));
    }
}

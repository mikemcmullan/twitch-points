<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Contracts\Repositories\ChannelRepository;
use App\Contracts\Repositories\TrackSessionRepository;
use App\Services\TwitchApi;
use App\Commands\StartSystemCommand;

class SyncSystemStatus extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'points:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    protected $channelRepository;

    protected $trackSessionRepository;

    protected $twitchApi;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChannelRepository $channelRepository, TrackSessionRepository $trackSessionRepository, TwitchApi $twitchApi)
    {
        parent::__construct();

        $this->channelRepository = $channelRepository;
        $this->trackSessionRepository = $trackSessionRepository;
        $this->twitchApi = $twitchApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $channel = $this->channelRepository->findBySlug($this->argument('channel'));

        if ($channel) {
            if (! $this->twitchApi->validChannel($channel->name)) {
                $this->error('Channel not valid.');
                return;
            }

            $status = $this->twitchApi->channelOnline($channel->name);
            $systemStatus = (bool) $this->trackSessionRepository->findIncompletedSession($channel);

            if (($status && ! $systemStatus) || ($systemStatus && ! $status)) {
                $this->dispatch(new StartSystemCommand($channel));
                $this->info('Starting / Stopping system for ' . $channel->slug);
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['channel', InputArgument::REQUIRED, 'Channel'],
        ];
    }
}

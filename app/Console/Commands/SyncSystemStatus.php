<?php

namespace App\Console\Commands;

use App\Channel;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
    protected $description = 'Sync the status of the system with the status of the twitch channel.';

    /**
     * @var TrackSessionRepository
     */
    protected $trackSessionRepository;

    /**
     * @var TwitchApi
     */
    protected $twitchApi;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->dispatch(new \App\Jobs\SyncSystemStatus);
    }
}

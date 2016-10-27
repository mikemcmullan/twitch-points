<?php

namespace App\Console\Commands;

use App\Channel;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\RemoveChannelJob;

class RemoveChannel extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:remove-channel {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all channel data from the system.';

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
    public function handle()
    {
        $channel = Channel::findBySlug($this->argument('slug'));

        if (! $channel) {
            $this->error(sprintf('Invalid channel %s', $this->argument('slug')));
            return;
        }

        if ($this->confirm("Are you sure you wish to delete the channel '{$channel->slug}' and all the associated data.")) {
            $this->dispatch(new RemoveChannelJob($channel));

            $this->info('Done');
        }
    }
}

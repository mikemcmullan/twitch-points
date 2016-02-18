<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\RemoveOldViewersJob;

class RemoveOldViewers extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:remove-old-viewers
        {channel : The slug of the channel. }
        {--D|days=30 : How many days must a viewer be inactive before being deleted. }
        {--P|points=5 : Only delete if they have less than [x] amount of points. }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old viewers from the channel.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $days = $this->option('days');
        $points = $this->option('points');
        $channel = \App\Channel::findBySlug($this->argument('channel'));

        if (! $channel) {
            $this->error(sprintf('Invalid channel %s', $this->argument('channel')));
            return;
        }

        $response = $this->dispatch(new RemoveOldViewersJob($channel, $days, $points));

        $this->info(sprintf('%s viewers have been deleted.', $response));
    }
}

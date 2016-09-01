<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\UpdateScoreboardCache as UpdateScoreboardCacheJob;
use App\Currency\CurrencyChannels;

class UpdateScoreboardCache extends Command
{
    use DispatchesJobs, CurrencyChannels;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:update-scoreboard-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the scoreboard cache.';

    /**
     * @var TrackSessionRepository
     */
    private $pointsSession;

    /**
     * Create a new command instance.
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
        foreach ($this->getActiveCurrencyChannels() as $channel) {
            $this->dispatch(new UpdateScoreboardCacheJob($channel));
        }
    }
}

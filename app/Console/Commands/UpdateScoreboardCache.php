<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\UpdateScoreboardCache as UpdateScoreboardCacheJob;

class UpdateScoreboardCache extends Command
{
    use DispatchesJobs;

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
     *
     * @param TrackSessionRepository $pointsSession
     */
    public function __construct(TrackSessionRepository $pointsSession)
    {
        parent::__construct();
        $this->pointsSession = $pointsSession;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sessions = $this->pointsSession->allIncompletedSessions();

        foreach ($sessions as $session) {
            $this->dispatch(new UpdateScoreboardCacheJob($session->channel));
        }
    }
}

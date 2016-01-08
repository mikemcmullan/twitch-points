<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\RankChattersJob;

class RankChatters extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'points:rank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rank Chatters.';

    /**
     * @var TrackSessionRepository
     */
    protected $pointsSession;

    /**
     * Create a new command instance.
     *
     * @return void
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
    public function fire()
    {
        $startTime = microtime(true);
        $sessions = $this->pointsSession->allIncompletedSessions();

        foreach ($sessions as $session) {
            $this->dispatch(new RankChattersJob($session->channel));
        }

        $end = microtime(true) - $startTime;
        \Log::info(sprintf('Ranked Chatters in %s seconds', $end));
        $this->info(sprintf('Ranked Chatters in %s seconds', $end));
    }
}

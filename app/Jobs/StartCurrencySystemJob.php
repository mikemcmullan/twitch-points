<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\TrackSessionRepository;
use App\Contracts\Repositories\ChatterRepository;
use Carbon\Carbon;
use App\Channel;

class StartCurrencySystemJob extends Job
{
    /**
     * @var User
     */
    public $channel;

    /**
     * Create a new job instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Handle the command.
     *
     * @param  StartSystemCommand $command
     * @return \App\Channel
     */
    public function handle(TrackSessionRepository $trackSessionRepository, ChatterRepository $chatterRepository)
    {
        $session = $trackSessionRepository->findIncompletedSession($this->channel);

        if (! $session) {
            setLastUpdate($this->channel, Carbon::now());
            return $trackSessionRepository->create($this->channel);
        }
    }
}

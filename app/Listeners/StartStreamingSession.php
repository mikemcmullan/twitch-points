<?php

namespace App\Listeners;

use App\Events\ChannelStartedStreaming;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\TrackSessionRepository;

class StartStreamingSession
{
    /**
     * @var TrackSessionRepository
     */
    protected $sessionRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(TrackSessionRepository $sessionRepo)
    {
        $this->sessionRepo = $sessionRepo;
    }

    /**
     * Handle the event.
     *
     * @param  ChannelStartedStreaming  $event
     * @return void
     */
    public function handle(ChannelStartedStreaming $event)
    {
        $session = $this->sessionRepo->findIncompletedSession($event->channel);

        if (! $session) {
            return $this->sessionRepo->create($event->channel);
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\ChannelStartedStreaming;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\StreamRepository;

class StartStreamingSession
{
    /**
     * @var StreamRepository
     */
    protected $streamRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(StreamRepository $streamRepo)
    {
        $this->streamRepo = $streamRepo;
    }

    /**
     * Handle the event.
     *
     * @param  ChannelStartedStreaming  $event
     * @return void
     */
    public function handle(ChannelStartedStreaming $event)
    {
        $stream = $this->streamRepo->findIncompletedStream($event->channel);

        if (! $stream) {
            return $this->streamRepo->create($event->channel);
        }
    }
}

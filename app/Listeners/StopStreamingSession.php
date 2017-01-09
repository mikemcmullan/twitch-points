<?php

namespace App\Listeners;

use App\Events\ChannelStoppedStreaming;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\StreamRepository;

class StopStreamingSession
{
    /**
     * @var StreamRepository
     */
    protected $streamRepo;

    /**
     * Create the event listener.
     *
     * @param StreamRepository $streamRepo
     */
    public function __construct(StreamRepository $streamRepo)
    {
        $this->streamRepo = $streamRepo;
    }

    /**
     * Handle the event.
     *
     * @param  ChannelStoppedStreaming  $event
     * @return bool|int
     */
    public function handle(ChannelStoppedStreaming $event)
    {
        $stream = $this->streamRepo->findIncompletedStream($event->channel);

        if ($stream) {
            return $this->streamRepo->end($stream);
        }
    }
}

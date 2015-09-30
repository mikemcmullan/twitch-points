<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveAwayWasReset extends Event implements ShouldBroadcast
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['private-' . $this->channel->name];
    }
}

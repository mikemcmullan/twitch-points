<?php

namespace App\Events\Giveaway;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveawayWasReset extends Event implements ShouldBroadcast
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
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'giveaway.was-reset';
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

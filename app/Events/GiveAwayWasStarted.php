<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveAwayWasStarted extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;

        $this->message = $channel->getSetting('giveaway.started-text');
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

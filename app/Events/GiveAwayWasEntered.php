<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveAwayWasEntered extends Event implements ShouldBroadcast
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var
     */
    public $handle;

    /**
     * @var
     */
    public $tickets;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param $handle
     * @param $tickets
     */
    public function __construct(Channel $channel, $handle, $tickets)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->tickets = $tickets;
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'giveaway.was-entered';
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

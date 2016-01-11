<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VIPsWasUpdated extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'bot.vips-were-updated';
    }

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param $handle
     * @param $tickets
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }
}

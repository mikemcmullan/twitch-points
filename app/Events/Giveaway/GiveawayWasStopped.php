<?php

namespace App\Events\Giveaway;

use App\Channel;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveawayWasStopped extends Event
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

        $this->message = $channel->getSetting('giveaway.stopped-text');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'response' => $this->message
        ];
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'giveaway.was-stopped';
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->channel->name];
    }
}

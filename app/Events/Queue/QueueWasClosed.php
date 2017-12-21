<?php

namespace App\Events\Queue;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class QueueWasClosed extends Event implements ShouldBroadcast
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param $name
     * @param $comment
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
        $this->message = $channel->getSetting('queue.closed-text');
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
        return 'queue.was-closed';
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

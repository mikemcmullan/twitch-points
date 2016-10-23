<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelUpdatedInfo extends Event
{
    use SerializesModels;

    public $channel;

    public $info;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Channel $channel, $info)
    {
        $this->channel = $channel;
        $this->info = $info;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}

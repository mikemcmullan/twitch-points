<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChannelUpdatedInfo extends Event
{
    use SerializesModels;

    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var array
     */
    public $info;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param $info
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

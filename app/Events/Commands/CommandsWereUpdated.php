<?php

namespace App\Events\Commands;

use App\Channel;
use App\Command;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommandsWereUpdated extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }
}

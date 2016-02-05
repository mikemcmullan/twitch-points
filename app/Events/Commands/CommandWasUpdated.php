<?php

namespace App\Events\Commands;

use App\Channel;
use App\Command;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommandWasUpdated extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var Command
     */
    public $command;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel, Command $command)
    {
        $this->channel = $channel;

        $this->command = $command;
    }
}

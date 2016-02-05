<?php

namespace App\Events\Commands;

use App\Channel;
use App\Command;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommandWasDeleted extends Event
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

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'bot.command-was-deleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->command->id
        ];
    }
}

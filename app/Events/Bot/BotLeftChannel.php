<?php

namespace App\Events\Bot;

use App\Channel;
use App\Events\Event;

class BotLeftChannel extends Event
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

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'command' => '!leave',
            'response' => $this->channel->getSetting('bot.outro', '')
        ];
    }
}

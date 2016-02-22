<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Timers\Timer;
use App\Channel;

class TimerWasExecuted extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var
     */
    public $timer;

    /**
     * Create a new event instance.
     *
     * @param Timer $timer
     * @return void
     */
    public function __construct(Timer $timer)
    {
        $this->channel = $timer->channel;

        $this->timer = $timer;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message'   => $this->timer->message,
            'interval'  => $this->timer->interval,
            'lines'     => $this->timer->lines
        ];
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'bot.timer';
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

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
     * @var
     */
    public $delay;

    /**
     * Create a new event instance.
     *
     * @param Timer $timer
     * @return void
     */
    public function __construct(Timer $timer, $delay = 0)
    {
        $this->channel = $timer->channel;
        $this->delay = $delay;
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
            'response'  => $this->timer->message,
            'interval'  => $this->timer->interval,
            'lines'     => $this->timer->lines,
            'delay'     => ($this->delay*1000)*60
        ];
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

    public function getModuleName()
    {
        return 'Timer';
    }
}

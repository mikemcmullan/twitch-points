<?php

namespace App\Events\Queue;

use App\Channel;
use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class QueueWasJoined extends Event implements ShouldBroadcast
{
    /**
     * @var Channel
     */
    private $channel;

	/**
     * @var
     */
    public $twitchId;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param $name
     * @param $comment
     */
    public function __construct(Channel $channel, $twitchId, $name, $comment)
    {
        $this->channel = $channel;
        $this->name = $name;
        $this->comment = $comment;
		$this->twitchId = $twitchId;
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'queue.was-joined';
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

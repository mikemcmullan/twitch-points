<?php

namespace App\Events;

use App\Events\Event;
use App\Channel;
use Illuminate\Support\Collection;

class NewFollower extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var Collection
     */
    protected $followers;

    /**
     * Create a new event instance.
     *
     * @param Channel   $channel
     * @param Array     $followers
     *
     * @return void
     */
    public function __construct(Channel $channel, $followers)
    {
        $this->channel = $channel;
        $this->followers = collect($followers);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $names = $this->followers->implode('display_name', ', ');

        $defaultString = "{{ followers }}, thanks for the follow" . ($this->followers->count() > 1 ? 's.' : '.');
        $string = $this->channel->getSetting('followers.welcome_msg', $defaultString);
        $string = preg_replace('/{{\s?followers\s?}}/', $names, $string);

        return [
            'response' => $string
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
}

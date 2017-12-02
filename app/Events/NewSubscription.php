<?php

namespace App\Events;

use App\Events\Event;
use App\Channel;
use Illuminate\Support\Collection;

class NewSubscription extends Event
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var array
     */
    public $subscriber;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param array $subscriber
     *
     */
    public function __construct(Channel $channel, array $subscriber)
    {
        $this->channel = $channel;
        $this->subscriber = $subscriber;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $string = '';

        if ($this->channel->getSetting('subscribers.display-alert-in-chat', false) === true) {
            $defaultString = 'Thanks for the sub {{ name }}.';
            $string = $this->channel->getSetting('subscribers.new-subscription-message', $defaultString);
            $string = preg_replace('/{{\s?name\s?}}/', $this->subscriber['display_name'], $string);
        }


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

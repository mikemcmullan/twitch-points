<?php

namespace App\Listeners;

use Illuminate\Contracts\Redis\Database;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushToBot
{
    /**
     * @var Database
     */
    private $redis;

    /**
     * Create the event listener.
     *
     * @param Database $redis
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    private function getCommand($event)
    {
        if (method_exists($event, 'broadcastAs')) {
            return $event->broadcastAs();
        }

        return null;
    }

    private function getData($event)
    {
        if (method_exists($event, 'broadcastWith')) {
            return $event->broadcastWith();
        }

        return null;
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $outEvent = [
            'source'    => 'web',
            'channel'   => "#{$event->channel->name}",
            'data'      => $this->getData($event)
        ];

        \Amqp::publish("commands.{$event->channel->name}", json_encode($outEvent), ['exchange' => "irc-messages"]);
    }
}

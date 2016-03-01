<?php

namespace App\Listeners;

use Illuminate\Contracts\Redis\Database;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Bschmitt\Amqp\Message;

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

        if (array_get($outEvent, 'data.delay') >= 0) {
            config(['amqp.properties.production.exchange_type' => 'x-delayed-message']);

            $msg = new Message(json_encode($outEvent), [
                'content_type' => 'application/json',
                'delivery_mode' => 2,
                'application_headers' => [
                    'x-delay' => ['I', array_get($outEvent, 'data.delay')]
                ]
            ]);

            \Amqp::publish("commands.mcsmike", $msg, ['exchange' => 'irc-messages-delayed']);

            config(['amqp.properties.production.exchange_type' => 'topic']);

            return;
        }

        \Amqp::publish("commands.{$event->channel->name}", json_encode($outEvent), ['exchange' => "irc-messages"]);
    }
}

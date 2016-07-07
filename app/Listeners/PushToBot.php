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

    protected function getData($event)
    {
        if (method_exists($event, 'broadcastWith')) {
            return $event->broadcastWith();
        }

        return null;
    }

    protected function getType($event)
    {
        if (method_exists($event, 'broadcastType')) {
            return $event->broadcastType();
        }

        return 'commands';
    }

    protected function getModuleName($event)
    {
        if (method_exists($event, 'getModuleName')) {
            return $event->getModuleName();
        }

        return 'Simple';
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $outEvent = array_merge([
            'source'    => 'web',
            'channel'   => "#{$event->channel->name}",
            'bot'       => $event->channel->getSetting('bot.username'),
            'module'    => $this->getModuleName($event),
            'byPassAuth'=> true
        ], $this->getData($event));

        $msg = new Message(json_encode($outEvent), [
            'content_type' => 'application/json',
            'delivery_mode' => 1,
            'application_headers' => [
                'x-delay' => ['I', array_get($outEvent, 'delay', 0)]
            ]
        ]);

        \Amqp::publish($this->getType($event), $msg, ['exchange' => 'irc-messages-delayed']);
    }
}

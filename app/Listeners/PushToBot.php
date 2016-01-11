<?php

namespace App\Listeners;

use App\Events\GiveAwayWasStarted;
use App\Events\GiveAwayWasStopped;
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
        if (method_exists($event, 'broadcastData')) {
            return $event->broadcastData();
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
            'channel'=> "#{$event->channel->name}",
            'command'=> $this->getCommand($event),
            'data' => $this->getData($event)
        ];

        $this->redis->publish('bot', json_encode($outEvent));
    }
}

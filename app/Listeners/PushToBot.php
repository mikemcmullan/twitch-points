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

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $outEvent = [
            'event' => 'Event::Announce',
            'data'  => [
                'server' => 'twitch-' . $event->channel->name,
                'channel'=> '#' . $event->channel->name,
                'message'=> $event->message
            ]
        ];

        $this->redis->lpush('bot-message-list', json_encode($outEvent));
    }
}

<?php

namespace App\Listeners;

use App\Events\ChannelUpdatedInfo;
use App\Stream;
use App\StreamMetric;

class UpdateChannelInfo
{
    /*
     * Create the event listener.
     *
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param ChannelUpdatedInfo $event
     */
    public function handle(ChannelUpdatedInfo $event)
    {
        $channel = $event->channel;
        $info = $event->info->first();

        $stream = Stream::where('channel_id', $channel->id)
            ->orderBy('created_at', 'DESC')
            ->take(1)
            ->first();

        if ($stream) {
            $metric = new StreamMetric([
                'viewers'   => $info['viewers'],
                'game'      => $info['game'],
                'title'     => $info['channel']['status']
            ]);

            $stream->metrics()->save($metric);
        }
    }
}

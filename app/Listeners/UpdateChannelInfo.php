<?php

namespace App\Listeners;

use App\Events\ChannelUpdatedInfo;

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

        $channel->viewers = $info['viewers'];
        $channel->game = $info['game'];
        $channel->save();
    }
}

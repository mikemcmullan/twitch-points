<?php

namespace App\Listeners;

use App\Events\ChannelUpdatedInfo;

class UpdateChannelInfo
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }
    /**
     * Handle the event.
     *
     * @param  VIPSWereUpdated  $event
     * @return void
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

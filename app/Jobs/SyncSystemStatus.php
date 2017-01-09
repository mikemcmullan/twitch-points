<?php

namespace App\Jobs;

use App\Channel;
use App\Services\TwitchApi;
use App\Contracts\Repositories\StreamRepository;
use App\Events\ChannelStartedStreaming;
use App\Events\ChannelStoppedStreaming;
use App\Events\ChannelUpdatedInfo;

class SyncSystemStatus extends Job
{
    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param Channel $channel
     * @param TwitchApi $twitchApi
     * @param StreamRepository $streamRepo
     */
    public function handle(Channel $channel, TwitchApi $twitchApi, StreamRepository $streamRepo)
    {
        $channels = $channel->all()->filter(function($channel) {
            return $channel->getSetting('active', false) == true;
        });

        if ($channels->count() === 0) {
            return;
        }

        $streams = collect($twitchApi->getStream($channels->pluck('channel_id')->toArray())['streams']);
        $channelStreams = $streams->groupBy('channel.name');

        foreach ($channels as $channel) {
            $currentChannelStatus = (bool) $streamRepo->findIncompletedStream($channel);
            $newStatus = (bool) $channelStreams->get($channel->name);

            // Only announce if the channel has started or stopped streaming and if the
            // status has changed since the last check.
            if (($newStatus && ! $currentChannelStatus) || ($currentChannelStatus && ! $newStatus)) {
                if ($currentChannelStatus) {
                    // echo $channel->name . ' ChannelStoppedStreaming' . PHP_EOL;
                    event(new ChannelStoppedStreaming($channel));
                } else {
                    // echo $channel->name . ' ChannelStartedStreaming' . PHP_EOL;
                    event(new ChannelStartedStreaming($channel));
                }
            }

            if ($channelInfo = $channelStreams->get($channel->name)) {
                event(new ChannelUpdatedInfo($channel, $channelInfo));
            }
        }
    }
}

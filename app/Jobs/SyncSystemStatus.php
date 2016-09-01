<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Services\TwitchApi;
use App\Exceptions\InvalidChannelException;
use App\Contracts\Repositories\TrackSessionRepository;
use App\Events\ChannelStartedStreaming;
use App\Events\ChannelStoppedStreaming;

class SyncSystemStatus extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Channel $channel, TwitchApi $twitchApi, TrackSessionRepository $trackSessionRepo)
    {
        $channels = $channel->all()->filter(function($channel) {
            return $channel->getSetting('active', false) == true;
        });

        if ($channels->count() === 0) {
            return;
        }

        $streams = collect($twitchApi->getStream($channels->implode('name', ','))['streams']);
        $channelStreams = $streams->groupBy('channel.name');

        foreach ($channels as $channel) {
            $currentChannelStatus = (bool) $trackSessionRepo->findIncompletedSession($channel);
            $newStatus = (bool) $channelStreams->get($channel->name);

            // Only announce if channel has started or stopped streaming if the
            // status has changed since the last check.
            if (($newStatus && ! $currentChannelStatus) || ($currentChannelStatus && ! $newStatus)) {
                if ($currentChannelStatus) {
                    // echo $channel->name . ' ChannelStoppedStreaming';
                    event(new ChannelStoppedStreaming($channel));
                    // $this->dispatch(new StopCurrencySystemJob($channel));
                } else {
                    // echo $channel->name . ' ChannelStartedStreaming';
                    event(new ChannelStartedStreaming($channel));
                    // $this->dispatch(new StartCurrencySystemJob($channel));
                }
            }
        }
    }
}

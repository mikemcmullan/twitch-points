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
            return $channel->getSetting('sync-system-status', false) == true;
        });

        foreach ($channels as $channel) {
            if (! $twitchApi->validChannel($channel->name)) {
                throw new InvalidChannelException;
            }

            $status = $twitchApi->channelOnline($channel->name);
            $systemStatus = (bool) $trackSessionRepo->findIncompletedSession($channel);

            if (($status && ! $systemStatus) || ($systemStatus && ! $status)) {
                $this->dispatch(new ToggleSystemJob($channel));
            }
        }
    }
}

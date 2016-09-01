<?php

namespace App\Listeners\Currency;

use App\Events\ChannelStoppedStreaming;
use App\Jobs\StopCurrencySystemJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

class StopCurrencySystem
{
    use DispatchesJobs;

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
     * @param  ChannelStartedStreaming  $event
     * @return void
     */
    public function handle(ChannelStoppedStreaming $event)
    {
        if ($event->channel->getSetting('currency.sync-status', false) === false) {
            return;
        }

        $this->dispatch(new \App\Jobs\StopCurrencySystemJob($event->channel));
    }
}

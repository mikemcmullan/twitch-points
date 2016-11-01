<?php

namespace App\Listeners\Currency;

use App\Events\ChannelStartedStreaming;
use App\Jobs\StartCurrencySystemJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Carbon\Carbon;

class StartCurrencySystem
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
    public function handle(ChannelStartedStreaming $event)
    {
        $this->dispatch(new \App\Jobs\StartCurrencySystemJob($event->channel));
    }
}

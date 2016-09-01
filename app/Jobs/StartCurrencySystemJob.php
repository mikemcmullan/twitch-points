<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use App\Channel;

class StartCurrencySystemJob extends Job
{
    /**
     * @var User
     */
    public $channel;

    /**
     * Create a new job instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        setLastUpdate($this->channel, Carbon::now());

        $this->channel->setSetting('currency.status', true);
    }
}

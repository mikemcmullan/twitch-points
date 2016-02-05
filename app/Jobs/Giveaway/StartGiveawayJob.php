<?php

namespace App\Jobs\Giveaway;

use App\Giveaway\Entry;
use App\Giveaway\Manager;
use App\Channel;
use Illuminate\Contracts\Bus\SelfHandling;

class StartGiveawayJob implements SelfHandling
{
    /**
     * @var
     */
    private $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        if (! $manager->isGiveAwayRunning($this->channel)) {
            return $manager->start($this->channel);
        }
    }
}

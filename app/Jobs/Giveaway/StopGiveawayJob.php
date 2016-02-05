<?php

namespace App\Jobs\Giveaway;

use App\Giveaway\Entry;
use App\Giveaway\Manager;
use Illuminate\Contracts\Bus\SelfHandling;
use App\Channel;

class StopGiveawayJob implements SelfHandling
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
        if ($manager->isGiveAwayRunning($this->channel)) {
            return $manager->stop($this->channel);
        }
    }
}

<?php

namespace App\Jobs\Giveaway;

use App\Jobs\Job;
use App\Giveaway\Entry;
use App\Giveaway\Manager;
use App\Channel;

class SelectGiveawayWinnerJob
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
        return $manager->selectWinner($this->channel);
    }
}

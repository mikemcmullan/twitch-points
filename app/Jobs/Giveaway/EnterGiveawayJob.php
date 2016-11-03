<?php

namespace App\Jobs\Giveaway;

use App\Jobs\Job;
use App\Giveaway\Entry;
use App\Giveaway\Manager;
use App\Channel;

class EnterGiveawayJob
{
    /**
     * @var
     */
    private $channel;

    /**
     * @var
     */
    private $handle;

    /**
     * @var
     */
    private $tickets;

    /**
     * @param Channel $channel
     * @param string $handle
     * @param int $tickets
     */
    public function __construct(Channel $channel, $handle, $tickets)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->tickets = $tickets;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        return $manager->enter(new Entry($this->channel, $this->handle, $this->tickets));
    }
}

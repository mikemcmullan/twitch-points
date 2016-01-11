<?php

namespace App\Jobs;

use App\Currency\Manager;
use App\Jobs\Job;

class RemoveCurrencyJob extends Job
{
    /**
     * @var
     */
    public $channel;

    /**
     * @var
     */
    public $handle;

    /**
     * @var
     */
    public $points;

    /**
     * Create a new command instance.
     *
     * @param string $channel  The channel the chatter belongs to.
     * @param string $handle   The chat handle of the user who will receive the points.
     * @param int $points      Amount of points to remove.
     */
    public function __construct($channel, $handle, $points)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->points = $points;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        return $manager->removePoints($this->channel, $this->handle, $this->points);
    }
}

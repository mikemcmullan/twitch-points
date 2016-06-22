<?php

namespace App\Jobs;

use App\Currency\Manager;
use App\Jobs\Job;

class AddCurrencyJob extends Job
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
     * @var
     */
    public $source;

    /**
     * Create a new command instance.
     *
     * @param string $channel  The channel the chatter belongs to.
     * @param string $handle   The chat handle of the user who will receive the points.
     * @param int $points      Amount of points to award.
     * @param string $source   The chat handle of the user who the points will be token from.
     */
    public function __construct($channel, $handle, $points, $source = null)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->points = $points;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        return $manager->addPoints($this->channel, $this->handle, $this->points, $this->source);
    }
}

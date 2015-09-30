<?php

namespace App\Jobs;

use App\Currency\Manager;
use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;

class AddCurrencyJob extends Job implements SelfHandling
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
    public $target;

    /**
     * Create a new command instance.
     *
     * @param string $channel  The channel the chatter belongs to.
     * @param string $handle   The chat handle of the user making the request.
     * @param string $target   The chat handle of the user who will receive the points.
     * @param int $points      Amount of points to award.
     */
    public function __construct($channel, $handle, $target, $points)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->points = $points;
        $this->target = $target;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        return $manager->addPoints($this->channel, $this->handle, $this->target, $this->points);
    }
}

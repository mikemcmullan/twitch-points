<?php

namespace App\Commands;

use App\Channel;
use App\Commands\Command;

class RemoveOldViewersCommand extends Command
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var
     */
    public $points;

    /**
     * @var
     */
    public $days;

    /**
     * Create a new command instance.
     *
     * @param Channel $channel
     * @param int $days             How many days must a viewer be inactive before being deleted.
     * @param int $points           Only delete if they have less than [x] amount of points.
     */
    public function __construct(Channel $channel, $days = 0, $points = 0)
    {
        $this->channel = $channel;
        $this->points = $points;
        $this->days = $days;
    }
}

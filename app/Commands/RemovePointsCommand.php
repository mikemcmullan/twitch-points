<?php

namespace App\Commands;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemovePointsCommand extends Command
{
    use InteractsWithQueue, SerializesModels;

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
     * @param $channel  The channel the chatter belongs to.
     * @param $handle   The chat handle of the user making the request.
     * @param $target   The chat handle of the user who will lose the points.
     * @param $points   Amount of points to award.
     */
    public function __construct($channel, $handle, $target, $points)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->points = $points;
        $this->target = $target;
    }
}

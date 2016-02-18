<?php

namespace App\Jobs;

use App\Currency\Manager;
use App\Jobs\Job;
use InvalidArgumentException;

class GetViewerJob extends Job
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
     * Create a new job instance.
     *
     * @param $channel
     * @param $handle
     */
    public function __construct($channel, $handle)
    {
        $this->channel = $channel;
        $this->handle = $handle;
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     */
    public function handle(Manager $manager)
    {
        if ($this->handle === null) {
            throw new InvalidArgumentException('Handle is a required parameter.');
        }

        $viewer = $manager->getViewer($this->channel, $this->handle);
        $viewer['channel'] = $viewer['channel']->name;
        $viewer['points'] = floor($viewer['points']);

        return array_only($viewer, ['channel', 'handle', 'points', 'minutes', 'rank', 'mod', 'admin']);
    }
}

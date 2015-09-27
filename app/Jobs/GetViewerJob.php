<?php

namespace App\Jobs;

use App\Currency\Manager;
use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use InvalidArgumentException;

class GetViewerJob extends Job implements SelfHandling
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

        $viewer = $manager->getPoints($this->channel, $this->handle);

        return [
            'channel'   => $viewer['channel']['name'],
            'handle'    => $viewer['handle'],
            'points'    => floor($viewer['points']),
            'minutes'   => (int) $viewer['minutes'],
            'rank'      => (int) array_get($viewer, 'rank'),
            'mod'       => (bool) array_get($viewer, 'mod')
        ];
    }
}

<?php

namespace App\Jobs\GiveAways;

use App\GiveAways\Entry;
use App\GiveAways\Manager;
use Illuminate\Contracts\Bus\SelfHandling;

class StartGiveawayJob implements SelfHandling
{
    /**
     * @var
     */
    private $channel;

    /**
     *
     */
    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function handle(Manager $manager)
    {
        if (! $manager->isGiveAwayRunning($this->channel)) {
            return $manager->start($this->channel);
        }
    }
}

<?php

namespace App\Jobs\GiveAways;

use App\GiveAways\Entry;
use App\GiveAways\Manager;
use Illuminate\Contracts\Bus\SelfHandling;

class ResetGiveawayJob implements SelfHandling
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
        return $manager->reset($this->channel);
    }
}

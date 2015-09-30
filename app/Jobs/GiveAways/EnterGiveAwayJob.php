<?php

namespace App\Jobs\GiveAways;

use App\GiveAways\Entry;
use App\GiveAways\Manager;
use Illuminate\Contracts\Bus\SelfHandling;

class EnterGiveAwayJob implements SelfHandling
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
     *
     */
    public function __construct($channel, $handle, $tickets)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->tickets = $tickets;
    }

    public function handle(Manager $manager)
    {
        return $manager->enter(new Entry($this->channel, $this->handle, $this->tickets));
    }
}
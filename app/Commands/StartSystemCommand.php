<?php

namespace App\Commands;

use App\Commands\Command;
use App\Channel;

class StartSystemCommand extends Command
{
    /**
     * @var User
     */
    public $channel;

    /**
     * Create a new command instance.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }
}

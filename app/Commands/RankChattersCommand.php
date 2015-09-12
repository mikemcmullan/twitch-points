<?php namespace App\Commands;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Channel;

class RankChattersCommand extends Command {

    use InteractsWithQueue, SerializesModels;

    /**
     * @var
     */
    public $channel;

    /**
     * Create a new command instance.
     *
     * @param $channel  
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

}

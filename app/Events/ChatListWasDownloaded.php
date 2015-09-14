<?php

namespace App\Events;

use App\Channel;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ChatListWasDownloaded extends Event
{
    use SerializesModels;

    /**
     * @var array
     */
    public $chatList;

    /**
     * @var bool
     */
    public $status;

    /**
     * @var Channel
     */
    public $channel;

    /**
     * Create a new event instance.
     *
     * @param Channel $channel
     * @param array $chatList
     * @param bool $status
     */
    public function __construct(Channel $channel, array $chatList, $status = true)
    {
        $this->chatList = $chatList;
        $this->status = $status;
        $this->channel = $channel;
    }
}

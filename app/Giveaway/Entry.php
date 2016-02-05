<?php

namespace App\Giveaway;

use App\Channel;

class Entry
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var string
     */
    private $handle;

    /**
     * @var int
     */
    private $tickets;

    /**
     * @param $channel
     * @param $handle
     * @param $tickets
     */
    public function __construct(Channel $channel, $handle, $tickets)
    {
        $this->channel = $channel;
        $this->handle = $handle;
        $this->tickets = $tickets;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return int
     */
    public function getTickets()
    {
        return (int) $this->tickets;
    }
}

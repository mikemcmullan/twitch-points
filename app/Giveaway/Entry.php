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
     * @var array
     */
    private $user;

    /**
     * @var int
     */
    private $tickets;

    /**
     * @param $channel
     * @param $handle
     * @param $tickets
     */
    public function __construct(Channel $channel, $user, $tickets)
    {
        $this->channel = $channel;
        $this->user = $user;
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getTickets()
    {
        return (int) $this->tickets;
    }
}

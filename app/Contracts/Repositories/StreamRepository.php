<?php

namespace App\Contracts\Repositories;

use App\Stream;
use App\Channel;

interface StreamRepository
{
    /**
     * Create a new stream.
     *
     * @param Channel $channel
     *
     * @return static
     */
    public function create(Channel $channel);

    /**
     * Complete a stream.
     *
     * @param Stream $stream
     *
     * @return bool|int
     */
    public function end(Stream $stream);

    /**
     * Find uncompleted track sessions for a user.
     *
     * @param Channel $channel
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findIncompletedStream(Channel $channel);

    /**
     * Find all uncompleted track sessions.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allIncompletedStreams();
}

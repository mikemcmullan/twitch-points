<?php

namespace App\Repositories\Stream;

use App\Stream;
use App\Contracts\Repositories\StreamRepository as StreamRepositoryInterface;
use App\Channel;
use Carbon\Carbon;

class EloquentStreamRepository implements StreamRepositoryInterface
{
    /**
     * @var Stream
     */
    private $stream;

    /**
     * @param Stream $stream
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Create a stream.
     *
     * @param Channel $channel
     *
     * @return static
     */
    public function create(Channel $channel)
    {
        return $this->stream->create([
            'channel_id' => $channel['id']
        ]);
    }

    /**
     * Complete a stream.
     *
     * @param Stream $stream
     *
     * @return bool|int
     */
    public function end(Stream $stream)
    {
        $streamLength = $stream['created_at']->diffInMinutes(Carbon::now());

        return $stream->update([
            'complete' => true,
            'stream_length' => $streamLength
        ]);
    }

    /**
     * Find uncompleted track sessions for a channel.
     *
     * @param Channel $channel
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findIncompletedStream(Channel $channel)
    {
        return $this->stream
            ->with('channel')
            ->where('complete', false)
            ->where('channel_id', $channel['id'])
            ->first();
    }

    /**
     * Find all uncompleted track sessions.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allIncompletedStreams()
    {
        return $this->stream
            ->with('channel')
            ->where('complete', false)
            ->get();
    }
}

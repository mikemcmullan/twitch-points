<?php

namespace App\Repositories\TrackPointsSession;

use App\TrackSession;
use App\Contracts\Repositories\TrackSessionRepository as TrackSessionRepositoryInterface;
use App\Channel;
use Carbon\Carbon;

class EloquentTrackSessionRepository implements TrackSessionRepositoryInterface {

    /**
     * @var TrackPointsSession
     */
    private $pointsSession;

	/**
     * @param TrackSession $pointsSession
     */
    public function  __construct(TrackSession $pointsSession)
    {
        $this->pointsSession = $pointsSession;
    }

    /**
     * Create a new track session.
     *
     * @param Channel $channel
     *
     * @return static
     */
    public function create(Channel $channel)
    {
        return $this->pointsSession->create([
            'channel_id' => $channel['id']
        ]);
    }

	/**
     * Complete a track session.
     *
     * @param TrackSession $session
     *
     * @return bool|int
     */
    public function end(TrackSession $session)
    {
        $streamLength = $session['created_at']->diffInMinutes(Carbon::now());

        return $session->update([
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
    public function findIncompletedSession(Channel $channel)
    {
        return $this->pointsSession
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
    public function allIncompletedSessions()
    {
        return $this->pointsSession
            ->with('channel')
            ->where('complete', false)
            ->get();
    }
}
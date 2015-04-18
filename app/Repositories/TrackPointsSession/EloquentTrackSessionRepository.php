<?php

namespace App\Repositories\TrackPointsSession;

use App\TrackSession;
use App\Contracts\Repositories\TrackSessionRepository as TrackSessionRepositoryInterface;
use App\User;
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
     * @param User $user
     *
     * @return static
     */
    public function create(User $user)
    {
        return $this->pointsSession->create([
            'user_id' => $user['id']
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
     * Find uncompleted track sessions for a user.
     *
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findUncompletedSession(User $user)
    {
        return $this->pointsSession
            ->with('user')
            ->where('complete', false)
            ->where('user_id', $user['id'])
            ->first();
    }

	/**
     * Find all uncompleted track sessions.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allUncompletedSessions()
    {
        return $this->pointsSession
            ->with('user')
            ->where('complete', false)
            ->get();
    }
}
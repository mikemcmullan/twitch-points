<?php

namespace App\Repositories\TrackPointsSessions;

use App\TrackPointsSession as Model;
use App\Repositories\TrackPointsSessions\TrackPointsSession as TrackPointsSessionInterface;
use Carbon\Carbon;

class EloquentTrackPointsSession implements TrackPointsSessionInterface {

    /**
     * @var TrackPointsSession
     */
    private $pointsSession;

    public function  __construct(Model $pointsSession)
    {
        $this->pointsSession = $pointsSession;
    }

    public function create($userId)
    {
        return $this->pointsSession->create([
            'user_id' => $userId
        ]);
    }

    public function end(Model $session)
    {
        $streamLength = $session['created_at']->diffInMinutes(Carbon::now());

        return $session->update([
            'complete' => true,
            'stream_length' => $streamLength
        ]);
    }

    public function allUncompletedSessions()
    {
        return $this->pointsSession
            ->with('user')
            ->where('complete', false)
            ->get();
    }
}
<?php namespace App\Http\Controllers;

use App\Commands\StartSystemCommand;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\ChatUsers\ChatUserRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;
use Illuminate\Http\Request;

class PointsController extends Controller {

	public function __construct()
    {
        $this->middleware('auth', ['except' => 'checkPoints']);
    }

    public function checkPoints(Request $request, ChatUserRepository $chatUserRepository)
    {
        $handle = $request->get('handle');
        $data = ['handle' => strtolower($handle)];

        if ($handle)
        {
            $channel = \Config::get('twitch.points.default_channel');
            $user = $chatUserRepository->user($channel, $handle);

            $data['user'] = ! empty($user) ? $user : [];
        }

        return view('check-points', $data);
    }

    public function systemControl(TrackSessionRepository $trackPointsSession)
    {
        $systemStarted = (bool) $trackPointsSession->findUncompletedSession(\Auth::user());

        return view('system-control', compact('systemStarted'));
    }

    public function startSystem()
    {
        $this->dispatch(new StartSystemCommand(\Auth::user()));

        return redirect()->back();
    }
}

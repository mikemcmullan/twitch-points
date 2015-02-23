<?php namespace App\Http\Controllers;

use App\Commands\StartSystemCommand;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\ChatUsers\ChatUserRepository;
use App\Repositories\ChatUsers\EloquentChatUserRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;
use App\Repositories\Users\UserRepository;
use Illuminate\Http\Request;

class PointsController extends Controller {

	public function __construct()
    {
        $this->middleware('auth', ['except' => 'checkPoints']);
    }

    public function checkPoints(Request $request, ChatUserRepository $chatUserRepository, UserRepository $userRepository)
    {
        $handle = $request->get('handle');
        $data = ['handle' => strtolower($handle)];

        if ($handle)
        {
            $channel = \Config::get('twitch.points.default_channel');
            $user = $userRepository->findByName($channel);
            
            $user = $chatUserRepository->user($user, $handle);

            $data['user'] = ! empty($user) ? $user : [];
        }

        return view('check-points', $data);
    }

    public function systemControl(TrackSessionRepository $trackPointsSession)
    {
        $systemStarted = (bool) $trackPointsSession->findUncompletedSession(\Auth::user());

        return view('system-control', compact('systemStarted'));
    }

    public function leaderboard(EloquentChatUserRepository $chatUser, UserRepository $userRepository)
    {
        $user = $userRepository->findByName('angrypug_');

        $data['chatUsers'] = $chatUser->allForUser($user);

        return view('leaderboard', $data);
    }

    public function startSystem()
    {
        $this->dispatch(new StartSystemCommand(\Auth::user()));

        return redirect()->back();
    }
}

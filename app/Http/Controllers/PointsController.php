<?php namespace App\Http\Controllers;

use App\Commands\StartSystemCommand;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\Chatters\ChatUserRepository;
use App\Repositories\Chatters\EloquentChatterRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;
use App\Repositories\Users\UserRepository;
use Illuminate\Http\Request;

class PointsController extends Controller {

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['checkPoints', 'scoreboard']]);
    }

    /**
     * Responds to GET request to check points.
     *
     * @param Request $request
     * @param EloquentChatterRepository $chatterRepository
     * @param UserRepository $userRepository
     *
     * @return \Illuminate\View\View
     */
    public function checkPoints(Request $request, EloquentChatterRepository $chatterRepository, UserRepository $userRepository)
    {
        $data = [
            'handle' => strtolower($request->get('handle')),
            'user' => null,
        ];

        $user = $userRepository->findByName(\Config::get('twitch.points.default_channel'));

        if ($data['handle'])
        {
            $data['user'] = $chatterRepository->findByHandle($user, $data['handle']);
        }

        $data['chatUsers'] = $chatterRepository->allForUser($user, 25);

        return view('check-points', $data);
    }

	/**
     * @param TrackSessionRepository $trackPointsSession
     *
     * @return \Illuminate\View\View
     */
    public function systemControl(TrackSessionRepository $trackPointsSession)
    {
        $systemStarted = (bool) $trackPointsSession->findUncompletedSession(\Auth::user());

        return view('system-control', compact('systemStarted'));
    }

	/**
     * @param EloquentChatterRepository $chatterRepository
     * @param UserRepository $userRepository
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(EloquentChatterRepository $chatterRepository, UserRepository $userRepository)
    {
        $user = $userRepository->findByName(\Config::get('twitch.points.default_channel'));

        $data['chatUsers'] = $chatterRepository->paginate(100)->allForUser($user);

        return view('scoreboard', $data);
    }

	/**
     * Responds to PATCH request to start the system.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startSystem()
    {
        $this->dispatch(new StartSystemCommand(\Auth::user()));

        return redirect()->back();
    }
}

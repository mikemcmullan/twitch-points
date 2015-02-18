<?php namespace App\Http\Controllers;

use App\Commands\StartSystemCommand;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\ChatUsers\ChatUserRepository;
use Illuminate\Http\Request;

class PointsController extends Controller {

	public function __construct()
    {
        \Auth::loginUsingId(1);
        \DB::enableQueryLog();
        $this->middleware('auth', ['except' => 'checkPoints']);
    }

    public function checkPoints(Request $request, ChatUserRepository $chatUserRepository)
    {
        $handle = $request->get('handle');
        $data = ['handle' => $handle];

        if ($handle)
        {
            $channel = \Config::get('twitch.points.default_channel');
            $user = $chatUserRepository->user($channel, $handle);

            $data['user'] = ! empty($user) ? $user : [];
        }

        return view('check-points', $data);
    }

    public function systemControl(Request $request)
    {
        $systemStarted = ! \Auth::user()['trackPoints']->isEmpty();

        return view('system-control', compact('systemStarted'));
    }

    public function startSystem()
    {
        $user = \Auth::user();

        $this->dispatch(new StartSystemCommand($user));

        return redirect()->back();
    }
}

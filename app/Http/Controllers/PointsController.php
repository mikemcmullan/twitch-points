<?php namespace App\Http\Controllers;

use App\Commands\StartSystemCommand;
use App\Contracts\Repositories\ChatterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\TrackSessionRepository;
use App\Contracts\Repositories\ChannelRepository;
use Illuminate\Http\Request;
use JasonGrimes\Paginator;

class PointsController extends Controller {

    /**
     * @var ChatterRepository
     */
    private $chatterRepository;
    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * @param ChatterRepository $chatterRepository
     * @param ChannelRepository $channelRepository
     */
    public function __construct(ChatterRepository $chatterRepository, ChannelRepository $channelRepository)
    {
        $this->middleware('auth', ['except' => ['checkPoints', 'scoreboard']]);
        $this->chatterRepository = $chatterRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * Responds to GET request to check points.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function checkPoints(Request $request)
    {
        $data = [
            'handle' => strtolower($request->get('handle')),
            'chatter' => null,
        ];

        $channel = $this->channelRepository->findByName(\Config::get('twitch.points.default_channel'));

        if ($data['handle'])
        {
            $data['chatter'] = $this->chatterRepository->findByHandle($channel, $data['handle']);
        }

        $data['chatters'] = $this->chatterRepository->paginate(1, 25)->allForChannel($channel, 25);

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
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(Request $request)
    {
        $user = $this->channelRepository->findByName(\Config::get('twitch.points.default_channel'));

        $data['chatters'] = $this->chatterRepository->paginate($request->get('page', 1), 100)->allForChannel($user);
        $data['chatterCount'] = $this->chatterRepository->getCountForChannel($user);
        $data['paginator'] = new Paginator($data['chatterCount'], 100, $request->get('page', 1), route('scoreboard_path') . '?page=(:num)');

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

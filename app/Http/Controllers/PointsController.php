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
     * @param Request $request
     * @param ChatterRepository $chatterRepository
     * @param ChannelRepository $channelRepository
     */
    public function __construct(Request $request, ChatterRepository $chatterRepository, ChannelRepository $channelRepository)
    {
        $this->middleware('auth', ['except' => ['checkPoints', 'scoreboard']]);
        $this->chatterRepository = $chatterRepository;
        $this->channelRepository = $channelRepository;
        $this->channel = $request->route()->getParameter('channel');
        // \Auth::loginUsingId(2, false);
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

        if ($data['handle']) {
            $data['chatter'] = $this->chatterRepository->findByHandle($this->channel, $data['handle']);
        }

        $data['chatters'] = $this->chatterRepository->paginate(1, 25)->allForChannel($this->channel, false, $this->channel->rank_mods);
        $data['channel'] = $this->channel;

        return view('check-points', $data);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(Request $request)
    {
        $data['channel'] = $this->channel;
        $data['chatters'] = $this->chatterRepository->paginate($request->get('page', 1), 100)->allForChannel($this->channel, false, $this->channel->rank_mods);
        $data['chatterCount'] = $this->chatterRepository->getCountForChannel($this->channel);
        $data['paginator'] = new Paginator($data['chatterCount'], 100, $request->get('page', 1), route('scoreboard_path', [$this->channel->slug]) . '?page=(:num)');

        return view('scoreboard', $data);
    }

    /**
     * @param TrackSessionRepository $trackPointsSession
     *
     * @return \Illuminate\View\View
     */
    public function systemControl(TrackSessionRepository $trackPointsSession)
    {
        $systemStarted = (bool) $trackPointsSession->findUncompletedSession($this->channel);
        $channel = $this->channel;

        return view('system-control', compact('systemStarted', 'channel'));
    }

	/**
     * Responds to PATCH request to start the system.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startSystem()
    {
        $this->dispatch(new StartSystemCommand($this->channel));

        return redirect()->back();
    }
}

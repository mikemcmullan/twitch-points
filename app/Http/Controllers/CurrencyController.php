<?php

namespace App\Http\Controllers;

use App\Jobs\ToggleSystemJob;
use App\Contracts\Repositories\ChatterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Http\Request;
use JasonGrimes\Paginator;
use App\Channel;

class CurrencyController extends Controller
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * @param Request $request
     * @param ChatterRepository $chatterRepository
     */
    public function __construct(Request $request, ChatterRepository $chatterRepository)
    {
        $this->middleware('auth', ['except' => ['checkPoints', 'scoreboard']]);
        $this->chatterRepository = $chatterRepository;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(Request $request, TrackSessionRepository $trackPointsSession, Channel $channel)
    {
        $data = [
            'handle' => strtolower($request->get('handle')),
            'chatter' => null,
        ];

        if ($data['handle']) {
            $data['chatter'] = $this->chatterRepository->findByHandle($channel, $data['handle']);
        }

        $data['page'] = $request->get('page', 1);
        $data['chatters'] = $this->chatterRepository->paginate($data['page'], 100)->allForChannel($channel, false, $channel->getSetting('rank-mods', false));
        $data['count'] = $data['chatters']->total();
        $data['status'] = (bool) $trackPointsSession->findIncompletedSession($channel);

        return view('scoreboard', $data);
    }
}

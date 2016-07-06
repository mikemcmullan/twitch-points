<?php

namespace App\Http\Controllers;

use App\Jobs\ToggleSystemJob;
use App\Support\ScoreboardCache;
use App\Contracts\Repositories\ChatterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Http\Request;
use JasonGrimes\Paginator;
use App\Channel;
use Cache;

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
    public function scoreboard(Request $request, TrackSessionRepository $trackPointsSession, Channel $channel, ScoreboardCache $scoreboardCache)
    {
        $data = [
            'handle' => strtolower($request->get('handle')),
            'chatter' => null,
        ];

        if ($data['handle']) {
            $data['chatter'] = $scoreboardCache->findByHandle($channel, $data['handle']);
        }

        $data['page'] = (int) $request->get('page', 1);
        $data['status'] = (bool) $trackPointsSession->findIncompletedSession($channel);
        $data['chatters'] = $scoreboardCache->paginate($data['page'])->allForChannel($channel);
        $data['count'] = $scoreboardCache->countForChannel($channel);
        $data['paginator'] = new Paginator($data['count'], 100, $data['page'], '/scoreboard?page=(:num)');

        return view('scoreboard', $data);
    }
}

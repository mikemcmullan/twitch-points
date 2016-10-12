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
        $this->middleware(['featureDetection:currency']);
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
            'handle'    => strtolower($request->get('handle')),
            'chatter'   => '{}'
        ];

        $api = new \App\Support\CallApi();

        if ($data['handle']) {
            $data['chatter'] = $api->viewer($channel->name, $data['handle']);
        }

        $data['status'] = (bool) $channel->getSetting('currency.status');
        $data['scoreboard'] = $api->currencyScoreboard($channel->name);

        return view('scoreboard', $data);
    }
}

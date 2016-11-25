<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\ScoreboardCache;
use App\Channel;
use App\Currency\Manager;
use Carbon\Carbon;

class CurrencyController extends Controller
{
    use DispatchesJobs;

    protected $currencyManager;

    /**
     *
     */
    public function __construct(Manager $manager)
    {
        $this->middleware(['jwt.auth', 'auth.api:currency'], ['except' => 'index']);

        $this->currencyManager = $manager;
    }

    /**
     * Get currency for all viewers.
     *
     * @param  Channel           $channel
     * @param  Request           $request
     * @param  ChatterRepository $chatterRepo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Channel $channel, Request $request, ScoreboardCache $scoreboardCache)
    {
        $results = $scoreboardCache->paginate($request->get('page', 1))->allForChannel($channel);

        return response()->json($results);
    }

    /**
     * Add currency to viewer.
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCurrency(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'points', 'source']);

        return response()->json($this->currencyManager->addPoints($channel, $data['handle'], $data['points'], $data['source']));
    }

    /**
     * Remove currency from viewer.
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeCurrency(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'points']);

        return response()->json($this->currencyManager->removePoints($channel, $data['handle'], $data['points']));
    }

    /**
     * Start currency system.
     *
     * @param Request $Request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function startSystem(Request $request, Channel $channel)
    {
        setLastUpdate($channel, Carbon::now());

        $channel->setSetting('currency.status', true);

        return response()->json(['ok' => 'success']);
    }

    /**
     * Stop currency system.
     *
     * @param Request $Request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stopSystem(Request $request, Channel $channel)
    {
        $channel->setSetting('currency.status', false);

        return response()->json(['ok' => 'success']);
    }
}

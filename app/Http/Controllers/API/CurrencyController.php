<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Channel;
use App\Http\Requests;
use App\Jobs\AddCurrencyJob;
use App\Jobs\RemoveCurrencyJob;
use App\Jobs\StopCurrencySystemJob;
use App\Jobs\StartCurrencySystemJob;
use Exception;
use InvalidArgumentException;
use App\Exceptions\UnknownHandleException;

class CurrencyController extends Controller
{
    use DispatchesJobs;

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
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
        $data = $request->only(['handle', 'points']);

        $response = $this->dispatch(new AddCurrencyJob($channel, $data['handle'], $data['points']));

        return response()->json($response);
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

        $response = $this->dispatch(new RemoveCurrencyJob($channel, $data['handle'], $data['points']));

        return response()->json($response);
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
        $this->dispatch(new StartCurrencySystemJob($channel));

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
        $this->dispatch(new StopCurrencySystemJob($channel));

        return response()->json(['ok' => 'success']);
    }
}

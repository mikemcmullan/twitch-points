<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Timers\Manager as TimerManager;
use App\Channel;

class TimersController extends Controller
{
    /**
     * @var TimeManager
     */
     private $timerManager;

    /**
     *
     */
    public function __construct(Request $request, TimerManager $timerManager)
    {
        $this->middleware(['jwt.auth', 'auth.api']);
        $this->timerManager = $timerManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel)
    {
        $timers = $this->timerManager->all($channel);

        return response()->json($timers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Channel $channel)
    {
        $timer = $this->timerManager->create($channel, $request->only(['name', 'interval', 'lines', 'message', 'disabled']));

        return response()->json($timer);
    }

    /**
     * Display the specified resource.
     *
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel, $id)
    {
        $timer = $this->timerManager->get($channel, $id);

        return response()->json($timer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel, $id)
    {
        $timer = $this->timerManager->update($channel, $id, $request->only(['name', 'interval', 'lines', 'message', 'disabled']));

        return response()->json($timer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel, $id)
    {
        $this->timerManager->delete($channel, $id);

        return response()->json(['ok' => 'success']);
    }
}

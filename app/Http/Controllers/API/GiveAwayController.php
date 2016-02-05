<?php

namespace App\Http\Controllers\API;

use App\Channel;
use App\Jobs\Giveaway\EnterGiveawayJob;
use App\Jobs\Giveaway\GetGiveawayEntriesJob;
use App\Jobs\Giveaway\StartGiveAwayJob;
use App\Jobs\Giveaway\StopGiveawayJob;
use App\Jobs\Giveaway\ResetGiveawayJob;
use App\Jobs\Giveaway\SelectGiveawayWinnerJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GiveawayController extends Controller
{
    /**
     *
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     *  Get giveaway entries.
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entries(Request $request, Channel $channel)
    {
        $entries = $this->dispatch(new GetGiveawayEntriesJob($channel));

        return response()->json($entries);
    }

    /**
     * Enter the giveaway.
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enter(Request $request, Channel $channel)
    {
        $handle = $request->get('handle');
        $tickets = $request->get('tickets');

        $response = $this->dispatch(new EnterGiveawayJob($channel, $handle, $tickets));

        return response()->json(['status' => $response]);
    }

    /*
     * Select a winner of the giveaway.
     *
     * @param Channel $channel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function winner(Channel $channel)
    {
        $winner = $this->dispatch(new SelectGiveawayWinnerJob($channel));

        return response()->json(['winner' => $winner]);
    }

    /*
     * Start the giveaway.
     *
     * @param Channel $channel
     */
    public function start(Channel $channel)
    {
        $this->dispatch(new StartGiveawayJob($channel));

        return response()->json(['ok' => 'success']);
    }

    /*
     * Stop the giveaway.
     *
     * @param Channel $channel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stop(Channel $channel)
    {
        $this->dispatch(new StopGiveawayJob($channel));

        return response()->json(['ok' => 'success']);
    }

    /*
     * Reset the giveaway.
     *
     * @param Channel $channel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reset(Channel $channel)
    {
        $this->dispatch(new ResetGiveawayJob($channel));

        return response()->json(['ok' => 'success']);
    }
}

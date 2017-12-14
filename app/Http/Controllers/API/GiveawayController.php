<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Channel;
use App\Giveaway\Manager;
use App\Giveaway\Entry;

class GiveawayController extends Controller
{
    /**
     *
     */
    public function __construct(Manager $manager)
    {
        $this->middleware(['jwt.auth', 'auth.api:quotes']);

        $this->giveawayManager = $manager;
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
        $entries = $this->giveawayManager->entries($channel, true);

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
        $user = $request->get('username');
        $tickets = $request->get('tickets');

        $response = $this->giveawayManager->enter(new Entry($channel, $user, $tickets));

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
        $winner = $this->giveawayManager->selectWinner($channel);

        return response()->json(['winner' => $winner]);
    }

    /*
     * Start the giveaway.
     *
     * @param Channel $channel
     */
    public function start(Channel $channel)
    {
        if (! $this->giveawayManager->isGiveAwayRunning($channel)) {
            $this->giveawayManager->start($channel);
        }

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
        if ($this->giveawayManager->isGiveAwayRunning($channel)) {
            $this->giveawayManager->stop($channel);
        }

        return response()->json(['ok' => 'success']);
    }

    /*
     * Clear giveaway entries.
     *
     * @param Channel $channel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clear(Channel $channel)
    {
        $this->giveawayManager->clear($channel);

        return response()->json(['ok' => 'success']);
    }
}

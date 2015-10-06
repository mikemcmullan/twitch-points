<?php

namespace App\Http\Controllers;

use App\Exceptions\GiveAwayException;
use App\GiveAways\Manager;
use Illuminate\Http\Request;

class GiveAwayController extends Controller
{
    /**
     * @var Manager
     */
    private $giveAwayManager;

    /**
     * @param Request $request
     * @param Manager $giveAwayManager
     */
    public function __construct(Request $request, Manager $giveAwayManager)
    {
        $this->middleware('auth');
        $this->channel = $request->route()->getParameter('channel');
        $this->giveAwayManager = $giveAwayManager;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $entries =  $this->giveAwayManager->entries($this->channel, true);

        $data['channel'] = $this->channel;
        $data['status'] = $this->giveAwayManager->isGiveAwayRunning($this->channel) ? 'Running' : 'Stopped';
        $data['entries'] = $entries->toJson();

        return view('giveaway.index', $data);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        $this->giveAwayManager->reset($this->channel);

        return redirect()->route('giveaway_path', $this->channel->name);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start()
    {
        if ( ! $this->giveAwayManager->isGiveAwayRunning($this->channel)) {
            $this->giveAwayManager->start($this->channel);
        }

        return redirect()->route('giveaway_path', $this->channel->name);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop()
    {
        if ($this->giveAwayManager->isGiveAwayRunning($this->channel)) {
            $this->giveAwayManager->stop($this->channel);
        }

        return redirect()->route('giveaway_path', $this->channel->name);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function winner()
    {
        try {
            $winner = $this->giveAwayManager->selectWinner($this->channel);

            return response()->json(['winner' => $winner]);
        } catch (GiveAwayException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function saveSettings(Request $request)
    {
        $ticketMax = (int) $request->input('ticket-max');
        $ticketCost = (int) $request->input('ticket-cost');

        if ($ticketCost < 0 || $ticketCost > 1000) {
            $ticketCost = 0;
        }

        if ($ticketMax < 0 || $ticketMax > 100) {
            $ticketMax = 10;
        }

        $giveawayStarted = substr(htmlentities($request->input('giveaway-started-text'), ENT_NOQUOTES, 'UTF-8', false), 0, 250);
        $giveawayStopped = substr(htmlentities($request->input('giveaway-stopped-text'), ENT_NOQUOTES, 'UTF-8', false), 0, 250);

        $this->channel->setSetting([
            'giveaway.ticket-max' => $ticketMax,
            'giveaway.ticket-cost' => $ticketCost,
            'giveaway.started-text' => $giveawayStarted,
            'giveaway.stopped-text' => $giveawayStopped
        ]);

        return redirect()
            ->back();
    }
}
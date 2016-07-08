<?php

namespace App\Http\Controllers;

use App\Exceptions\GiveAwayException;
use App\Giveaway\Manager;
use App\Channel;
use App\Command;
use Illuminate\Http\Request;

class GiveawayController extends Controller
{
    /**
     * @var Manager
     */
    private $giveawayManager;

    /**
     * @param Request $request
     * @param Manager $giveawayManager
     */
    public function __construct(Request $request, Manager $giveawayManager)
    {
        $this->middleware('auth');
        $this->giveawayManager = $giveawayManager;
    }

    /**
     * @param Channel Channel
     * @return \Illuminate\View\View
     */
    public function index(Channel $channel)
    {
        if (\Gate::denies('access-page', [$channel, 'giveaway'])) {
            return $this->redirectHomeWithMessage();
        }

        $data['status'] = $this->giveawayManager->isGiveAwayRunning($channel) ? 'Running' : 'Stopped';

        return view('giveaways', $data);
    }
}

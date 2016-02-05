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
        $data['status'] = $this->giveawayManager->isGiveAwayRunning($channel) ? 'Running' : 'Stopped';
        $data['apiToken'] = \JWTAuth::fromUser(\Auth::user());
        $data['enterCommand'] = Command::where(['file', 'Giveaway', 'channel_id' => $channel->id])->first();

        return view('giveaways', $data);
    }
}

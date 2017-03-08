<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Channel;

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
    public function __construct(Request $request)
    {
        $this->middleware(['featureDetection:currency']);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function scoreboard(Request $request, Channel $channel)
    {
        $data = [
            'username'  => strtolower($request->get('username')),
            'chatter'   => '{}'
        ];

        $api = new \App\Support\CallApi();

        if ($data['username']) {
            $data['chatter'] = $api->viewer($channel->slug, $data['username']);
        }

        $data['status'] = (bool) $channel->getSetting('currency.status');
        $data['scoreboard'] = $api->currencyScoreboard($channel->slug);

        return view('scoreboard', $data);
    }
}

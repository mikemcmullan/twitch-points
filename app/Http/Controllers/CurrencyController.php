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
            'handle'    => strtolower($request->get('handle')),
            'chatter'   => '{}'
        ];

        $api = new \App\Support\CallApi();

        if ($data['handle']) {
            $data['chatter'] = $api->viewer($channel->slug, $data['handle']);
        }

        $data['status'] = (bool) $channel->getSetting('currency.status');
        $data['scoreboard'] = $api->currencyScoreboard($channel->slug);

        return view('scoreboard', $data);
    }
}

<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;

class CommandsController extends Controller
{
    /*
     * @var Channel
     */
    private $channel;

    /*
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // $this->middleware('protect.api');
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommands()
    {
        return $this->channel->commands->map(function($item) {
            return array_except($item, ['channel_id', 'created_at', 'updated_at']);
        });
    }
}

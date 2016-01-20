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
        $results = $this->channel->commands()->with('strings')->get();

        return $results->map(function ($command) {
            $array = array_only($command->toArray(), ['id', 'pattern', 'level', 'type', 'file', 'response']);

            $command->strings->each(function ($string) use (&$array) {
                $array['strings'][$string->name] = $string->value;
            });

            return $array;
        });
    }
}

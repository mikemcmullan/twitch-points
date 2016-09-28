<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;
use Carbon\Carbon;

class ChatLogsController extends Controller
{
    /*
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // $this->middleware(['jwt.auth', 'auth.api']);
    }

    public function index(Channel $channel, Request $request)
    {
        try {
            $date = Carbon::createFromTimestamp($request->get('starting-from', Carbon::now()->timestamp));
        } catch (\Exception $e) {
            $date = Carbon::now();
        }

        $messages = \App\ChatLogs::where('channel', $channel->name)
            ->where('created_at', '<', $date)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(500);

        $messages->each(function ($message) use ($twitch, $bttv) {
            unset($message->command_id);

            if (! $message->display_name) {
                $message->display_name = $message->username;
            }
        });

        return response($messages);
    }
}

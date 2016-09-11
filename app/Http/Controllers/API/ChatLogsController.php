<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;

class ChatLogsController extends Controller
{
    /*
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->middleware(['jwt.auth', 'auth.api']);
    }

    public function index(Channel $channel)
    {
        $bttv = new \App\EmoteReplacer\Replacers\BetterTTV($channel);
        $twitch = new \App\EmoteReplacer\Replacers\Twitch();

        $messages = \App\ChatLogs::where('channel', $channel->name)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(100);

        $messages->each(function ($message) use ($twitch, $bttv) {
            unset($message->command_id);

            if ($cache = \Cache::get('chatLogMessage-' . md5($message->id))) {
                $message->message = $cache;
            } else {
                $message->message = $twitch->replace($message);
                $message->message = $bttv->replace($message);
                \Cache::put('chatLogMessage-' . md5($message->id), $message->message, 60*60*24);
            }
        });

        return response($messages);
    }
}

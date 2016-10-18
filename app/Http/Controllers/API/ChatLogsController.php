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

    /**
     * Search chat logs.
     *
     * @param  Channel $channel
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function search(Channel $channel, Request $request)
    {
        $term = $request->get('term');
        $limit = (int) $request->get('limit', 500) ?: 500;

        $messages = \App\ChatLogs::where('channel', $channel->name)
            ->where('message', 'LIKE', "%{$term}%")
            ->orderBy('created_at', 'DESC')
            ->simplePaginate($limit);

        return response()->json($messages);
    }

    /**
     * Get chat logs around a provided date.
     *
     * @param  Channel $channel
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function conversation(Channel $channel, Request $request)
    {
        $date = $request->get('date');

        $first = \DB::table('chat_logs')
            ->where('channel', $channel->name)
            ->where('created_at', '>=', $date)
            ->orderBy('created_at', 'asc')
            ->take(20);

        $second = \DB::table('chat_logs')
            ->where('channel', $channel->name)
            ->where('created_at', '<', $date)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->union($first);

        $results = \DB::table(\DB::raw("({$second->toSql()}) as r"))
            ->mergeBindings($second)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => ($results)]);
    }

    /**
     * Get chat logs.
     *
     * @param  Channel $channel
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Channel $channel, Request $request)
    {
        $date = $request->get('starting-from', Carbon::now()) ?: Carbon::now();
        $limit = (int) $request->get('limit', 500) ?: 500;

        $direction = in_array($request->get('direction'), ['older', 'newer']) ? $request->get('direction') : 'older';
        $arrow = $direction === 'older' ? '<' : '>';

        $messages = \App\ChatLogs::where('channel', $channel->name)
            ->where('created_at', $arrow, $date)
            ->orderBy('created_at', $direction === 'older' ? 'desc' : 'asc')
            ->simplePaginate($limit);

        return response($messages);
    }
}

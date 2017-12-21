<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Channel;
use Redis;
use App\Currency\Manager;

class QueueController extends Controller
{
    /**
     *
     */
    public function __construct()
    {
        $this->middleware(['jwt.auth','auth.api:queue']);
		$this->middleware('resolveTwitchUsername', ['only' => ['add']]);
    }

	public function index(Request $request, Channel $channel)
	{
		$default = [
			'name' 			=> 'Game',
			'keyword' 		=> '!join',
			'level'			=> 'everyone',
			'level_argument'=> 0,
			'cost'			=> 0
		];

		$response = $channel->getSetting('queue', $default);
		$response['status'] = $this->isQueueOpen($channel) ? 'open' : 'closed';
        $response['entrants'] = $this->getEntrants($channel);

		return response()->json($response, 200);
	}

	public function open(Request $request, Channel $channel)
	{
		if ($this->isQueueOpen($channel)) {
			return response()->json([
                'status' => 409,
                'message' => 'Queue is already open.'
            ], 409);
		}

		$this->openQueue($channel);

        event(new \App\Events\Queue\QueueWasOpened($channel));

		return response()->json(['success' => true]);
	}

	public function close(Request $request, Channel $channel)
	{
		$this->closeQueue($channel);

        event(new \App\Events\Queue\QueueWasClosed($channel));

		return response()->json(['success' => true]);
	}

	public function add(Request $request, Channel $channel, Manager $currencyManager)
	{
		$user = $request->get('username');
        $comment = substr($request->get('comment', ''), 0, 200);

		if (! $this->isQueueOpen($channel)) {
            return response()->json([
                'status' => 409,
                'message' => 'Queue is not open.'
            ], 409);
		}

		if (! $user) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid user.'
            ], 400);
		}

        if (Redis::hget("{$channel->id}:queue", $user['twitch_id']) !== null) {
            return response()->json([
                'status' => 409,
                'message' => 'Already in queue.'
            ], 409);
		}

        $level = $channel->getSetting('queue.level');
        $levelArg = (int) $channel->getSetting('queue.level_argument', 0);
        $cost = (int) $channel->getSetting('queue.cost', 0);
        $viewer = $currencyManager->getViewer($channel, $user['twitch_id']);

        if ($level === 'min_currency') {
            if ($viewer['points'] < $levelArg) {
                return response()->json(['message' => 'Not enough currency.'], 409);
            }
        }

        if ($level === 'min_time') {
            if ($viewer['minutes'] < $levelArg) {
                return response()->json(['message' => 'Have not spent enough time in the channel.'], 409);
            }
        }

        if ($cost > 0) {
            $currencyManager->remove($channel, $user['twitch_id'], $cost);
        }

		$result = Redis::hset("{$channel->id}:queue", $user['twitch_id'], $comment);

        event(new \App\Events\Queue\QueueWasJoined($channel, $user['twitch_id'], $user['display_name'], $comment));

		if (! $result) {
            return response()->json([
                'status' => 409,
                'message' => 'Already in queue.'
            ], 409);
		}

		return response()->json(['success' => true]);
	}

    public function clear(Request $request, Channel $channel)
    {
        Redis::del("{$channel->id}:queue");

        return response()->json(['success' => true]);
    }

	public function remove(Request $request, Channel $channel)
	{
		$twitchId = $request->get('twitchId');

		if (! $this->isQueueOpen($channel)) {
            return response()->json([
                'status' => 409,
                'message' => 'Queue is not open.'
            ], 409);
		}

		if ($twitchId) {
			$result = Redis::hdel("{$channel->id}:queue", "{$twitchId}");
		} else {
			$result = false;
		}


		if (! $result) {
            return response()->json([
                'status' => 409,
                'message' => 'Was not in queue.'
            ], 409);
		}

		return response()->json(['success' => true]);
	}

    protected function getEntrants($channel)
    {
        $entrants = Redis::hgetall("{$channel->id}:queue");
        $es = [];

        foreach ($entrants as $twitchId => $comment) {
            $e = json_decode(Redis::hget('twitch:chatUsers', $twitchId), true);

            $es[] = [
                'id'           => $twitchId,
                'display_name' => $e['display_name'],
                'comment'      => $comment
            ];
        }

        return $es;
    }

	protected function openQueue($channel)
	{
		return Redis::set("{$channel->id}:queueOpen", true);
	}

	protected function isQueueOpen($channel)
	{
		return (bool) Redis::get("{$channel->id}:queueOpen");
	}

	protected function closeQueue($channel)
	{
		return Redis::set("{$channel->id}:queueOpen", false);
	}
}

<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;
use App\Bot;
use Cache;
use Carbon\Carbon;
use App\Events\Bot\BotJoinedChannel;
use App\Events\Bot\BotLeftChannel;

class BotController extends Controller
{
    /*
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->middleware(['jwt.auth', 'auth.api']);
    }

	public function joinChannel(Channel $channel)
	{
        $disableJoiningKey = $channel->slug . ':disableJoining';
        $reJoinWaitTime = 1; // minutes
        $status = $this->getStatus($channel);

        // Check if bot is already in channel?
        if ($status === 'in_channel') {
            return response()->json([
                'error'     => 'Conflict',
                'status'    => 409,
                'message'   => 'The bot has already joined the channel.'
            ], 409);
        }

        if ($status === 'unavailable') {
            return response()->json([
                'error'     => 'Conflict',
                'status'    => 409,
                'message'   => 'The bot is currently unavailable.'
            ], 409);
        }

        // Check if not enough time has passed since last join request.
        if (Cache::get($disableJoiningKey)) {
            return response()->json([
                'error'     => 'Conflict',
                'status'    => 409,
                'message'   => sprintf('Please wait a %d minute(s) before trying to rejoin the channel.', $reJoinWaitTime)
            ]);
        }

        // Send join request.
        \Event::fire(new BotJoinedChannel($channel));
        $channel->bots()->attach($channel->bot->id);
        Cache::put($disableJoiningKey, 1, $reJoinWaitTime);

		return response()->json(['ok' => 'success']);
	}

	public function leaveChannel(Channel $channel)
	{
        // Check if bot is already in channel?
        if ($this->getStatus($channel) !== 'in_channel') {
            return response()->json([
                'error'     => 'Conflict',
                'status'    => 409,
                'message'   => 'The bot is not in the channel.'
            ], 409);
        }

        \Event::fire(new BotLeftChannel($channel));
        // $channel->setSetting('bot.in-channel', false);
        $channel->bots()->detach();

		return response()->json(['ok' => 'success']);
	}

	public function status(Channel $channel)
	{
		return response()->json([
			'status' => $this->getStatus($channel)
		]);
	}

    public function updateStatus(Request $request)
    {
        $status = $request->get('status');

        if (! in_array($status, ['available', 'unavailable'])) {
            return response()->json([
                'error'     => 'Conflict',
                'status'    => 409,
                'message'   => 'available and unavailable are the only valid statuses.'
            ], 404);
        }

        $bot = Bot::where('name', $request->get('bot'))->first();

        if (! $bot) {
            return response()->json([
                'error'     => 'Not Found',
                'status'    => 404,
                'message'   => "Bot '{$request->get('bot')}' does not exist."
            ], 404);
        }

        if ($bot && $bot->status !== $status) {
            $bot->update(['status' => $status]);

            if ($status === 'unavailable') {
                $bot->channels()->detach();
            }

            \Log::info($bot->name . ' has been set to: ' . $request->get('status'));
        }

        return response()->json(['ok' => 'success']);
    }

    protected function getStatus(Channel $channel)
    {
        $channel->load('bots');

        if ($channel->bot->status !== 'available') {
            return 'unavailable';
        }

        if ($channel->bots->isEmpty()) {
            return 'not_in_channel';
        }

        return 'in_channel';
    }
}

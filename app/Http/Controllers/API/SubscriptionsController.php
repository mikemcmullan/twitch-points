<?php

namespace App\Http\Controllers\API;

use App\Channel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Events\NewSubscription;
use App\Events\ReSubscription;

class SubscriptionsController extends Controller
{
    public function __construct()
    {
		$this->middleware(['jwt.auth', 'auth.api']);
		$this->middleware('resolveTwitchUsername', ['only' => ['newSubscription', 'reSubscription']]);
    }

	/**
	 * When someone subscribes to the channel.
	 *
	 * @param  Request $request
	 * @param  Channel $channel
	 * @return
	 */
	public function newSubscription(Request $request, Channel $channel)
	{
		$username = request()->get('username');

	    if (! $username) {
	        return response()->json(['error' => 'Invalid user.']);
	    }

	    event(new NewSubscription($channel, ['id' => $username['twitch_id'], 'display_name' => $username['display_name'], 'username' => $username['username']]));

		return response()->json(['success' => true]);
	}

	/**
	 * When someone resubscribes to the channel.
	 *
	 * @param  Request $request [description]
	 * @param  Channel $channel [description]
	 * @return
	 */
	public function reSubscription(Request $request, Channel $channel)
	{
		$username = $request->get('username');
		$months = $request->get('months', 2);

	    if (! $username) {
	        return response()->json(['error' => 'Invalid user.']);
	    }

		event(new ReSubscription($channel, ['id' => $username['twitch_id'], 'display_name' => $username['display_name'], 'username' => $username['username'], 'months' => $months]));

		return response()->json(['success' => true]);
	}
}

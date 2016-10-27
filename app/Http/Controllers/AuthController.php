<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\AuthenticateUser;
use App\Services\TwitchSDKAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Channel;
use Carbon\Carbon;

class AuthController extends Controller
{
    private $channel;

    public function __construct(Request $request)
    {

    }

    /**
     * @param Request $request
     * @param TwitchSDKAdapter $twitchSDK
     *
     * @return \Illuminate\Http\RedirectResponse|Response|\Illuminate\Routing\Redirector
     */
    public function loginProxy(Request $request, TwitchSDKAdapter $twitchSDK)
    {
        if (! $request->get('code') && ! $request->get('error')) {
            $data    = $request->only(['referer', 'sig', 'expires']);
            $url     = $twitchSDK->authLoginURL('user_read');

            $response    = new Response(view('login-proxy', compact('url')));
            $response->header('Location', $url);
            $response->withCookie(cookie('referer', $data['referer'], 60));
            $response->withCookie(cookie('sig', $data['sig'], 60));
            $response->withCookie(cookie('expires', $data['expires'], 60));

            return $response;
        }

        $referer = $request->cookie('referer');
        $sig     = $request->cookie('sig');
        $expires   = $request->cookie('expires');

        try {
            $future = Carbon::createFromTimestamp($expires)->isFuture();
        } catch (\Exception $e) {
            return response('Error, invalid nonce.');
        }

        if (! $future) {
            return response('Error, to much time has past since the authentication process began.');
        }

        $key = config('app.key');
        $signature = hash_hmac('sha256', $referer . $key . $expires, $key);

        if ($signature !== $sig) {
            return response('Error, signature mismatch.');
        }

        return redirect($referer  . '?' . $request->getQueryString());
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @param Channel $channel
     * @param AuthenticateUser $authUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request, Channel $channel, AuthenticateUser $authUser)
    {
        if (\Auth::check()) {
            return redirect()
                ->route('home_path', $channel->slug)
                ->with('message', 'You are already logged in.');
        }

        if (! $request->get('code') && ! $request->get('error')) {
            $referer = $request->url();
            $expires = Carbon::now()->addMinutes(5)->timestamp;
            $key = config('app.key');
            $signature = hash_hmac('sha256', $referer . $key . $expires, $key);

            return redirect(route('login_proxy_path', ['referer=' . $referer, 'sig=' . $signature, 'expires=' . $expires]));
        } else {
            return $authUser->execute($channel, $request->get('code'), $request->get('error'), $this);
        }
    }

    /**
     * Logout a user.
     *
     * @param Channel $channel
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Channel $channel)
    {
        \Auth::logout();

        return redirect()
            ->route('home_path', $channel->slug)
            ->with('message', 'You have been successfully logged out.');
    }

    /**
     * @param Channel $channel
     * @param $error
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginHasFailed(Channel $channel, $error)
    {
        return redirect()
            ->route('home_path', $channel->slug)
            ->with('message', 'Sorry, you\'re not allowed to administrate this site.');
    }

    /**
     * @param Channel $channel
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userHasLoggedIn(Channel $channel, $user)
    {
        return redirect()
            ->route('home_path', $channel->slug)
            ->with('message', 'Login successful.');
    }

    /**
     * @param Request $request
     * @param Channel $channel
     * @param \Pusher $pusher
     * @return Response
     */
    public function pusher(Request $request, Channel $channel, \Pusher $pusher)
    {
        $data = $request->only(['socket_id', 'channel_name']);

        $user = \Auth::user();

        if ($user && $user->can('admin-channel', $channel)) {
            return response($pusher->socket_auth($data['channel_name'], $data['socket_id']));
        }

        abort(403, 'Forbidden');
    }
}

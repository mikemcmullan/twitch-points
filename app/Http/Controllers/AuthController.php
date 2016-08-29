<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\AuthenticateUser;
use App\Services\TwitchSDKAdapter;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Channel;
use Carbon\Carbon;

class AuthController extends Controller
{
    private $channel;

    public function __construct(Request $request)
    {
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * @param Request $request
     * @param TwitchSDKAdapter $twitchSDK
     * @param Encrypter $encrypter
     *
     * @return \Illuminate\Http\RedirectResponse|Response|\Illuminate\Routing\Redirector
     */
    public function loginProxy(Request $request, TwitchSDKAdapter $twitchSDK, Encrypter $encrypter)
    {
        if (! $request->get('code') && ! $request->get('error')) {
            $data    = $request->only(['referer', 'sig', 'nonce']);
            $url     = $twitchSDK->authLoginURL('user_read');

            $response    = new Response(view('login-proxy', compact('url')));
            $response->header('Location', $url);
            $response->withCookie(cookie('referer', $data['referer'], 5));
            $response->withCookie(cookie('sig', $data['sig'], 5));
            $response->withCookie(cookie('nonce', $data['nonce'], 5));

            return $response;
        }

        $referer = $request->cookie('referer');
        $sig     = $request->cookie('sig');
        $nonce   = $request->cookie('nonce');

        try {
            $diff = Carbon::createFromTimestampUTC($nonce)->diffInMinutes(Carbon::now());
        } catch (\Exception $e) {
            return response('Error, invalid nonce.');
        }

        if (in_array($diff, range(0, 5)) === false) {
            return response('Error, to much time has past since the authentication process began.');
        }

        $key = config('app.key');
        $signature = hash_hmac('sha256', $referer . $key . $nonce, $key);

        if ($signature !== $sig) {
            return response('Error, signature mismatch.');
        }

        return redirect($referer  . '?' . $request->getQueryString());
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @param AuthenticateUser $authUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request, Channel $channel, AuthenticateUser $authUser, Encrypter $encrypter)
    {
        if (! $request->get('code') && ! $request->get('error')) {
            $referer = $request->fullUrl();
            $nonce = time();
            $key = config('app.key');
            $signature = hash_hmac('sha256', $referer . $key . $nonce, $key);

            return redirect(route('login_proxy_path', [$channel->slug, 'referer=' . $referer, 'sig=' . $signature, 'nonce=' . $nonce]));
        } else {
            return $authUser->execute($this->channel, $request->get('code'), $request->get('error'), $this);
        }
    }

    /**
     * Logout a user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        \Auth::logout();

        return redirect()->route('home_path', $this->channel->slug);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginHasFailed()
    {
        return redirect()
            ->route('home_path', $this->channel->slug)
            ->with('message', 'Sorry, you\'re not allowed to administrate this site.');
    }

    /**
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userHasLoggedIn($user)
    {
        return redirect()
            ->route('home_path', $this->channel->slug)
            ->with('message', 'Login successful.');
    }

    /**
     * @param Request $request
     * @param \Pusher $pusher
     * @return Response
     */
    public function pusher(Request $request, \Pusher $pusher)
    {
        $data = $request->only(['socket_id', 'channel_name']);

        $user = \Auth::user();

        if ($user && $user->hasPermission('giveaway')) {
            return response($pusher->socket_auth($data['channel_name'], $data['socket_id']));
        }

        abort(403, 'Forbidden');
    }
}

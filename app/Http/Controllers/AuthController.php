<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\AuthenticateUser;
use App\Services\TwitchSDKAdapter;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
            $referer    = $request->get('referer');
            $url        = $twitchSDK->authLoginURL('user_read');

            $response    = new Response(view('login-proxy', compact('url')));
            $response->header('Location', $url);
            $response->withCookie(cookie('referer', $referer, 60*60));

            return $response;
        }

        $referer = $request->cookie('referer');

        if (! $referer) {
            return response('Error redirecting, please use your browsers back button to return the site.');
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
    public function login(Request $request, AuthenticateUser $authUser)
    {
        if (! $request->get('code') && ! $request->get('error')) {
            return redirect('http://' . env('AUTH_DOMAIN', 'auth.twitch.dev') . '/login?referer=' . $request->fullUrl());
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

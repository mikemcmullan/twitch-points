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
        $args = $request->only(['code', 'scope', 'state', 'error', 'error_description']);
        $slug = cache()->get('authState:' . $args['state']);

        if (! $slug) {
            return response('Unable to complete the login process, a possible reason could be the login process took to long. Please start again.', 403);
        }

        if ($args['error']) {
            $redirectArgs = [
                $slug,
                "error={$args['error']}",
                "error_description={$args['error_description']}",
                "state={$args['state']}"
            ];
        } else {
            $redirectArgs = [
                $slug,
                "code={$args['code']}",
                "scope={$args['scope']}",
                "state={$args['state']}"
            ];
        }

        return redirect()->route('login_callback_path', $redirectArgs);
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @param Channel $channel
     * @param AuthenticateUser $authUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request, Channel $channel)
    {
        if (\Auth::check()) {
            return redirect()
                ->route('home_path', $channel->slug)
                ->with('message', 'You are already logged in.');
        }

        $redirect = \Socialite::driver('twitch')->redirect();
        cache()->put('authState:' . session()->get('state'), $channel->slug, 15);

        return $redirect;
    }

    /**
     * After receiving the oauth code from twitch complete the oauth flow
     * and login the user.
     *
     * @param  Request          $request
     * @param  Channel          $channel
     * @param  AuthenticateUser $authUser
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginCallback(Request $request, Channel $channel, AuthenticateUser $authUser)
    {
        if ($error = $request->get('error')) {
            return $this->loginHasFailed($channel, $error);
        }

        return $authUser->execute($channel, $this);
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginHasFailed(Channel $channel, $error = false)
    {
        switch ($error) {
            case 'access_denied':
                $errorMsg = 'Sorry, you have not allowed us access to your twitch profile.';
                break;

            default:
                $errorMsg = 'Sorry, you\'re not allowed to administrate this site.';
                break;
        }

        return redirect()
            ->route('home_path', $channel->slug)
            ->with('message', $errorMsg);
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

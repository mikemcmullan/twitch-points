<?php

namespace App\Services;

use App\Channel;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Socialite;
use Gate;

class AuthenticateUser
{
    /**
     * @var TwitchSDKAdapter
     */
    private $twitchSDK;

    /**
     * @var Authenticator
     */
    private $auth;

    /**
     * @param TwitchSDKAdapter $twitchSDK
     * @param Guard $auth
     */
    public function __construct(TwitchSDKAdapter $twitchSDK, Guard $auth)
    {
        $this->twitchSDK = $twitchSDK;
        $this->auth = $auth;
    }

    /**
     * @param $channel
     * @param $code
     * @param $error
     * @param $listener
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function execute(Channel $channel, $listener)
    {
        $authUser = Socialite::driver('twitch')->user();
        $user = User::findByServiceId($authUser->id);

        if (Gate::forUser($user)->denies('admin-channel', $channel)) {
            return $listener->loginHasFailed($channel);
        }

        $user->update([
            'name'          => $authUser->name,
            'display_name'  => $authUser->nickname,
            'email'         => $authUser->email,
            'access_token'  => $authUser->token,
            'logo'          => $authUser->avatar
        ]);

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($channel, $user);
    }
}

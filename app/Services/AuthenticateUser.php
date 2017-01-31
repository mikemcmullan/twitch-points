<?php

namespace App\Services;

use App\Channel;
use App\User;
use Illuminate\Contracts\Auth\Guard;

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
    public function execute(Channel $channel, $code, $error, $listener)
    {
        // If error login failed.
        if ($error) {
            return $listener->loginHasFailed($channel);
        }

        $token = $this->twitchSDK->authAccessTokenGet($code);

        // If error is returned from twitch the access token will be missing.
        if (! isset($token['access_token'])) {
            return $listener->loginHasFailed($channel);
        }

        $authUser = $this->twitchSDK->authUserGet($token['access_token']);

        $user = User::findByName($authUser['name']);

        if (\Gate::forUser($user)->denies('admin-channel', $channel)) {
            return $listener->loginHasFailed($channel);
        }

        $user->update([
            'access_token'  => $token['access_token'],
            'service_id'    => $authUser['_id'],
            'logo'          => $authUser['logo']
        ]);

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($channel, $user);
    }
}

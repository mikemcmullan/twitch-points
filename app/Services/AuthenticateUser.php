<?php

namespace App\Services;

use App\Contracts\Repositories\ChannelRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Cookie\CookieJar;

class AuthenticateUser {

    /**
     * @var TwitchSDKAdapter
     */
    private $twitchSDK;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * @var CookieJar
     */
    private $cookieJar;

    /**
     * @var Authenticator
     */
    private $auth;

    /**
     * @param TwitchSDKAdapter $twitchSDK
     * @param ChannelRepository $channelRepository
     * @param CookieJar $cookieJar
     * @param Guard $auth
     */
    public function __construct(TwitchSDKAdapter $twitchSDK, ChannelRepository $channelRepository, CookieJar $cookieJar, Guard $auth)
    {
        $this->twitchSDK = $twitchSDK;
        $this->channelRepository = $channelRepository;
        $this->cookieJar = $cookieJar;
        $this->auth = $auth;
    }

    /**
     * @param $code
     * @param $error
     * @param $listener
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function execute($code, $error, $listener)
    {
        // If error login failed.
        if ($error) return $listener->loginHasFailed();

        // If no code redirect to twitch login.
        if ( ! $code) return $this->getAuthorizationFirst();

        $token = $this->twitchSDK->authAccessTokenGet($code);

        // If error is returned from twitch the access token will be missing.
        if ( ! isset($token['access_token'])) return $listener->loginHasFailed();

        $authUser = $this->twitchSDK->authUserGet($token['access_token']);

        $user = $this->channelRepository->findByNameOrCreate($authUser['name'], [
            'email'         => $authUser['email'],
            'logo'          => $authUser['logo'],
            'access_token'  => $token['access_token']
        ]);

        if ($user['access_token'] !== $token['access_token'])
        {
            $user['access_token'] = $token['access_token'];

            $this->channelRepository->update($user);
        }

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($user);
    }

    /**
     * Redirect user to twitch login page.
     *
     * @return \Illuminate\Routing\Redirector
     */
    private function getAuthorizationFirst()
    {
        return redirect($this->twitchSDK->authLoginURL('user_read'));
    }
}
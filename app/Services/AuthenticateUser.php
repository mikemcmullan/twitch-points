<?php

namespace App\Services;

use App\Channel;
use App\Contracts\Repositories\ChannelRepository;
use App\Contracts\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Cookie\CookieJar;

class AuthenticateUser
{
    /**
     * @var TwitchSDKAdapter
     */
    private $twitchSDK;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * @var Authenticator
     */
    private $auth;

    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @param TwitchSDKAdapter $twitchSDK
     * @param ChannelRepository $channelRepo
     * @param UserRepository $userRepo
     * @param Guard $auth
     */
    public function __construct(TwitchSDKAdapter $twitchSDK, ChannelRepository $channelRepo, UserRepository $userRepo, Guard $auth)
    {
        $this->twitchSDK = $twitchSDK;
        $this->channelRepo = $channelRepo;
        $this->auth = $auth;
        $this->userRepo = $userRepo;
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
            return $listener->loginHasFailed($error);
        }

        $token = $this->twitchSDK->authAccessTokenGet($code);

        // If error is returned from twitch the access token will be missing.
        if (! isset($token['access_token'])) {
            return $listener->loginHasFailed();
        }

        $authUser = $this->twitchSDK->authUserGet($token['access_token']);

        $user = $this->userRepo->findByName($channel, $authUser['name']);

        if (! $user) {
            return $listener->loginHasFailed();
        }

        if ($user['access_token'] !== $token['access_token']) {
            $user['access_token'] = $token['access_token'];
            $this->userRepo->update($user);
        }

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($user);
    }
}

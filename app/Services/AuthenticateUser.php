<?php

namespace App\Services;

use App\Channel;
use App\Contracts\Repositories\UserRepository;
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
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @param TwitchSDKAdapter $twitchSDK
     * @param UserRepository $userRepo
     * @param Guard $auth
     */
    public function __construct(TwitchSDKAdapter $twitchSDK, UserRepository $userRepo, Guard $auth)
    {
        $this->twitchSDK = $twitchSDK;
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
            return $listener->loginHasFailed($channel, $error);
        }

        $token = $this->twitchSDK->authAccessTokenGet($code);

        // If error is returned from twitch the access token will be missing.
        if (! isset($token['access_token'])) {
            return $listener->loginHasFailed($channel);
        }

        $authUser = $this->twitchSDK->authUserGet($token['access_token']);

        $user = $this->userRepo->findByName($channel, $authUser['name']);

        if (\Gate::forUser($user)->denies('admin-channel', $channel)) {
            return $listener->loginHasFailed($channel);
        }

        if ($user['access_token'] !== $token['access_token']) {
            $user['access_token'] = $token['access_token'];
            $this->userRepo->update($user);
        }

        $this->auth->login($user, true);

        return $listener->userHasLoggedIn($channel, $user);
    }
}

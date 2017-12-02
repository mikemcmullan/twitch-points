<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ChannelStartedStreaming::class => [
            \App\Listeners\StartStreamingSession::class
        ],

        \App\Events\ChannelStoppedStreaming::class => [
            \App\Listeners\StopStreamingSession::class
        ],

        \App\Events\ChannelUpdatedInfo::class => [
            \App\Listeners\UpdateChannelInfo::class
        ],

        \App\Events\ChatListWasDownloaded::class => [
            \App\Listeners\RetrieveUserInfo::class,
            \App\Listeners\ProcessChatList::class,
            \App\Listeners\UpdateDeModdedChatters::class
        ],

        \App\Events\Commands\CommandWasUpdated::class => [
            \App\Listeners\Commands\UpdateCommandBotCache::class
        ],

        \App\Events\Commands\CommandWasDeleted::class => [
            \App\Listeners\Commands\DeleteCommandFromBotCache::class
        ],

        \App\Events\Commands\CommandsWereUpdated::class => [
            \App\Listeners\Commands\UpdateCommandsBotCache::class
        ],

        \App\Events\VIPsWereUpdated::class => [
            \App\Listeners\UpdateVIPsBotCache::class
        ],

        \App\Events\Giveaway\GiveawayWasStarted::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\Giveaway\GiveawayWasStopped::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\TimerWasExecuted::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\Bot\BotJoinedChannel::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\Bot\BotLeftChannel::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\NewFollower::class => [
            \App\Listeners\AwardCurrency::class,
            \App\Listeners\PushToBot::class
            \App\Listeners\PushToBot::class
        ],

        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\Twitch\TwitchExtendSocialite@handle',
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\SettingsEventListener'
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen('tymon.jwt.absent', function () {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 400,
                'message' => 'token_not_provided'
            ], 400);
        });

        Event::listen('tymon.jwt.expired', function ($e) {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 403,
                'message' => 'token_expired'
            ], 403);
        });

        Event::listen('tymon.jwt.invalid', function ($e) {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 403,
                'message' => 'token_invalid'
            ], 403);
        });

        Event::listen('tymon.jwt.user_not_found', function () {
            return response()->json([
                'error' => 'Not Found',
                'status'=> 404,
                'message' => 'user_not_found'
            ], 404);
        });
    }
}

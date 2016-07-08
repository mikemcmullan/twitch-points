<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ChatListWasDownloaded::class => [
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
        ]
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
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        $events->listen('tymon.jwt.absent', function () {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 400,
                'message' => 'token_not_provided'
            ], 400);
        });

        $events->listen('tymon.jwt.expired', function ($e) {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 403,
                'message' => 'token_expired'
            ], 403);
        });

        $events->listen('tymon.jwt.invalid', function ($e) {
            return response()->json([
                'error' => 'Forbidden',
                'status'=> 403,
                'message' => 'token_invalid'
            ], 403);
        });

        $events->listen('tymon.jwt.user_not_found', function () {
            return response()->json([
                'error' => 'Not Found',
                'status'=> 404,
                'message' => 'user_not_found'
            ], 404);
        });
    }
}

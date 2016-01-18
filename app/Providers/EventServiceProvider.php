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
            \App\Handlers\Events\ProcessChatList::class
        ],

        \App\Events\CommandWasUpdated::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\VIPsWasUpdated::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\BettingWasUpdated::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\GiveAwayWasStarted::class => [
            \App\Listeners\PushToBot::class
        ],

        \App\Events\GiveAwayWasStopped::class => [
            \App\Listeners\PushToBot::class
        ]
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

        //
    }
}

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
        'irc-bot.command.points' => [
            'App\Bot\Commands\GetPoints'
        ],

        'App\Events\ChatListWasDownloaded' => [
            'App\Handlers\Events\ProcessChatList'
        ],

        'App\Events\GiveAwayWasStarted' => [
            'App\Listeners\PushToBot'
        ],

        'App\Events\GiveAwayWasStopped' => [
            'App\Listeners\PushToBot'
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

<?php

namespace App\Providers;

use App\Repositories\Chatter\EloquentChatterRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Contracts\Repositories\ChatterRepository',
            'App\Repositories\Chatter\MySQLChatterRepository'
        );

//        $this->app->bind(
//            'App\Contracts\Repositories\ChannelRepository',
//            'App\Repositories\Channel\EloquentChannelRepository'
//        );

        $this->app->bind(
            'App\Contracts\Repositories\StreamRepository',
            'App\Repositories\Stream\EloquentStreamRepository'
        );

        $this->app->bind(
            'App\Contracts\Repositories\UserRepository',
            'App\Repositories\User\EloquentUserRepository'
        );
    }
}

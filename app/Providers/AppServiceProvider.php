<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Pusher', function ($app) {
            $appKey = env('PUSHER_KEY');
            $appSecret = env('PUSHER_SECRET');
            $appId = env('PUSHER_APP_ID');

            return new \Pusher($appKey, $appSecret, $appId, ['encrypted' => true]);
        });
    }
}

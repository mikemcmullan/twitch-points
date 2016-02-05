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
        view()->composer('*', function($view){
            $view->with('user', \Auth::user());
            view()->share('channel', request()->route()->getParameter('channel'));
        });
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
            $appKey = config('services.pusher.key');
            $appSecret = config('services.pusher.secret');
            $appId = config('services.pusher.app_id');

            return new \Pusher($appKey, $appSecret, $appId, ['encrypted' => true]);
        });
    }
}

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
        // Validation rule to check if value is greater than param 1 and less than param 2.
        \Validator::extend('numeric_size_between', function ($attribute, $value, $parameters, $validator) {
            return ($value >= $parameters[0]) && ($value <= $parameters[1]);
        });

        \Validator::replacer('numeric_size_between', function ($message, $attribute, $rule, $parameters) {
            return str_replace([':min', ':max'], $parameters, $message);
        });

        // Validation rule to check if value contains only alpha numeric characters,
        // dashes, underscores or spaces.
        \Validator::extend('alpha_dash_space', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[a-z-_\s0-9]+$/i', $value);
        });

        view()->composer('*', function($view){
            $view->with('user', \Auth::user());
            $view->with('channel', request()->route()->getParameter('channel'));

            if (\Auth::check()) {
                $view->with('apiToken', \JWTAuth::fromUser(\Auth::user()));
            } else {
                $view->with('apiToken', '');
            }
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

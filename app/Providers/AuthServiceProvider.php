<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-channel', function ($user, $channel) {
            if (! $channel) {
                return false;
            }

            return ! $user->channels->isEmpty();
        });

        Gate::define('access-page', function ($user, $channel, $feature) {
            return $channel->hasFeature($feature) && $user->can('admin-channel', $channel);
        });

        \Auth::provider('custom', function ($app) {
            return $app->make(\App\Providers\Auth\CustomUserProvider::class, ['model' => $app['config']['auth']['providers']['users']['model']]);
        });
    }
}

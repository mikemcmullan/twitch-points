<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
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
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        parent::registerPolicies($gate);

        $gate->define('admin-channel', function ($user, $channel) {
            if (! $channel) {
                return false;
            }

            return ! $user->channels->isEmpty();
        });

        $gate->define('access-page', function ($user, $channel, $page) {
            return $user->hasPermission($page) && $user->can('admin-channel', $channel);
        });

        \Auth::provider('custom', function ($app) {
            return $app->make(\App\Providers\Auth\CustomUserProvider::class, ['model' => $app['config']['auth']['providers']['users']['model']]);
        });
    }
}

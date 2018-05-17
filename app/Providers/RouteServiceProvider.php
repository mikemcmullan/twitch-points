<?php

namespace App\Providers;

use App\Channel;
use App\Exceptions\InvalidChannelException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @internal param Router $router
     */
    public function boot()
    {
        Route::bind('channel', function ($value) {
            $channel = Channel::findBySlug($value);

            if (! $channel) {
                throw new InvalidChannelException;
            }

            return $channel;
        });

        parent::boot();
    }


    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapAuthRoutes();

        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'domain' => '{channel}.' . config('app.root_domain'),
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'domain' => config('app.api_domain'),
            'prefix' => '{channel}',
            'middleware' => 'api',
            'namespace' => $this->namespace . '\API'
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    protected function mapAuthRoutes()
    {
        Route::group([
            'domain' => config('app.auth_domain'),
            'prefix' => '',
            'middleware' => 'twitch-auth',
            'namespace' => $this->namespace
        ], function ($router) {
            require base_path('routes/auth.php');
        });
    }
}

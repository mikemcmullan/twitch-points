<?php

namespace App\Providers;

use DB;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Bus\Dispatcher  $dispatcher
     * @return void
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->mapUsing(function ($command) {
            return Dispatcher::simpleMapping(
                $command, 'App\Commands', 'App\Handlers\Commands'
            );
        });

        $dispatcher->pipeThrough([function ($command, $next) {
            if (get_class($command) === 'App\Commands\UpdatePoints') {
                return DB::transaction(function () use ($command, $next) {
                    return $next($command);
                });
            }

            return $next($command);
        }]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

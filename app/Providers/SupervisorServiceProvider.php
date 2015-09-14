<?php

namespace App\Providers;

use fXmlRpc\Client;
use fXmlRpc\Transport\HttpAdapterTransport;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Ivory\HttpAdapter\GuzzleHttpHttpAdapter;
use Supervisor\Connector\XmlRpc;
use Supervisor\Supervisor;

class SupervisorServiceProvider extends ServiceProvider
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
        $this->app->bind('Supervisor\Supervisor', function ($app) {
            $username = env('SUPERVISOR_USER');
            $password = env('SUPERVISOR_PASSWORD');

            $client = new Client(
                env('SUPERVISOR_RPC'),
                new HttpAdapterTransport(
                    new GuzzleHttpHttpAdapter(
                        new GuzzleClient([
                            'defaults' => [
                                'auth' => [$username, $password]
                            ]
                        ])
                    )
                )
            );

            $connector = new XmlRpc($client);

            return new Supervisor($connector);
        });
    }
}

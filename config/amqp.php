<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Define which configuration should be used
    |--------------------------------------------------------------------------
    */

    'use' => 'production',

    /*
    |--------------------------------------------------------------------------
    | AMQP properties separated by key
    |--------------------------------------------------------------------------
    */

    'properties' => [

        'production' => [
            'host'                => env('AMQP_HOST', 'localhost'),
            'port'                => env('AMQP_PORT', 5672),
            'username'            => env('AMQP_USERNAME', 'guest'),
            'password'            => env('AMQP_PASSWORD', 'guest'),
            'vhost'               => '/',
            'exchange'            => 'amq.topic',
            'exchange_type'       => 'x-delayed-message',
            'exchange_durable'    => true,
            'consumer_tag'        => 'consumer',
            'queue_properties'    => ['x-ha-policy' => ['S', 'all']],
            'exchange_properties' => [],
            'connect_options'     => [],
            'ssl_options'         => [],
            'timeout'             => 0
        ],

    ],

];

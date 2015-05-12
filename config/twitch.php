<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twitch Api Credentials
    |--------------------------------------------------------------------------
    |
    | The client id, secret and redirect uri for you twitch app.
    |
    | Located here: http://www.twitch.tv/settings/connections
    |
    */
    'credentials' => [
        'client_id'     => env('TWITCH_CLIENT_ID'),
        'client_secret' => env('TWITCH_CLIENT_SECRET'),
        'redirect_uri'  => env('TWITCH_REDIRECT_URI'),
    ],

    'points' => [

        /*
        |--------------------------------------------------------------------------
        | Default Channel
        |--------------------------------------------------------------------------
        |
        | If a user provides another channel use it, but if none is provided
        | use the default channel.
        |
        */
        'default_channel' => 'angrypug_',
        
        /*
        |--------------------------------------------------------------------------
        | Points Awarded / Interval
        |--------------------------------------------------------------------------
        |
        | How many points are awards per interval in minutes. The interval can
        | change depending on if the channel is online or offline.
        |
        | ex. The chatter is awarded 5 points per 15 minutes while the streamer
        |     is online. But only 1 point per 30 minutes if the streamer is
        |     offline.
        |
        */
        'online' => [
            'interval' => 15,
            'awarded' => 1,
        ],


        'offline' => [
            'interval' => 60,
            'awarded' => 1
        ]
    ]

];
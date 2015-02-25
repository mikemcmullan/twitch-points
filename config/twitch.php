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
        | Hidden Chatters
        |--------------------------------------------------------------------------
        |
        | A list of chat handles that shouldn't be shown on the scoreboard.
        |
        */
        'hidden_chatters' => ['angrypug_', 'nightbot', 'angrypugbot'],

        /*
        |--------------------------------------------------------------------------
        | Points Awarded / Interval
        |--------------------------------------------------------------------------
        |
        | How many points are awards per interval in minutes.
        |
        | ex. For every 5 minutes of being online the user is rewarded 1 point.
        |
        */
        'interval' => 15,
        'awarded' => 5,

        /*
        |--------------------------------------------------------------------------
        | Points Awarded To New Users.
        |--------------------------------------------------------------------------
        |
        | How many points are awarded to a new user.
        |
        */
        'award_new' => 0

    ]

];
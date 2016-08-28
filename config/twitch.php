<?php

return [

    'chat_list_api' => 'https://tmi.twitch.tv/group/user/%s/chatters',
    // 'chat_list_api' => 'http://mcsmike.twitch.dev/chatters',

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

    /*
    |--------------------------------------------------------------------------
    | Follower Notifications
    |
    | The url, username and password for the follower notification server.
    |--------------------------------------------------------------------------
     */
    'follower_notifications' => [
        'enabled'   => env('FOLLOW_NOTIFICATION_ENABLED'),
        'url'       => env('FOLLOW_NOTIFICATION_URL'),
        'username'  => env('FOLLOW_NOTIFICATION_USERNAME'),
        'password'  => env('FOLLOW_NOTIFICATION_PASSWORD'),
    ]

];

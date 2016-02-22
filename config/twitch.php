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

];

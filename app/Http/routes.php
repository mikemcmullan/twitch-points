<?php

Route::group(['domain' => config('app.auth_domain')], function () {
    Route::get('/login', [
        'uses'  => 'AuthController@loginProxy',
        'as'    => 'login_proxy_path'
    ]);
});

Route::group(['domain' => config('app.api_domain'), 'namespace' => 'API'], function () {
    Route::put('/bot/status', [
        'uses'  => 'BotController@updateStatus'
    ]);
});

Route::group(['domain' => config('app.api_domain'), 'prefix' => '{channel}', 'namespace' => 'API', 'middleware' => ['api']], function () {
    Route::get('/viewer', [
        'uses'  => 'ViewerController@getViewer',
        'as'    => 'api_points_path'
    ]);

    Route::get('/vips', [
        'uses'  => 'GeneralController@getVIPs',
        'as'    => 'api_vips_path'
    ]);

    /*
     * Follower Routes
     */
     Route::post('/followers', [
         'uses' => 'FollowersController@store'
     ]);

    /*
     * Commands Routes
     */
    Route::get('/commands', [
        'uses'  => 'CommandsController@index',
        'as'    => 'api_commands_path'
    ]);

    Route::get('/commands/{id}', [
        'uses'  => 'CommandsController@show',
        'as'    => 'api_commands_show_path'
    ]);

    Route::post('/commands', [
        'uses' => 'CommandsController@store',
        'as'   => 'api_commands_store_path'
    ]);

    Route::put('/commands/{id}', [
        'uses' => 'CommandsController@update',
        'as'   => 'api_commands_update_path'
    ]);

    Route::delete('/commands/{id}', [
        'uses' => 'CommandsController@destroy',
        'as'   => 'api_commands_destroy_path'
    ]);

    /**
     * Chat Logs Routes
     */
     Route::get('/chat-logs', [
         'uses' => 'ChatLogsController@index',
         'as'   => 'api_chatLogs_index_path'
     ]);

     Route::get('/chat-logs/search', [
         'uses' => 'ChatLogsController@search',
         'as'   => 'api_chatLogs_search_path'
     ]);

     Route::get('/chat-logs/conversation', [
         'uses' => 'ChatLogsController@conversation',
         'as'   => 'api_chatLogs_conversation_path'
     ]);

    /**
     * Timers Routes
     */
     Route::get('/timers', [
         'uses' => 'TimersController@index',
         'as'   => 'api_timers_path'
     ]);

     Route::get('/timers/{id}', [
         'uses'  => 'TimersController@show',
         'as'    => 'api_timers_show_path'
     ]);

     Route::post('/timers', [
         'uses' => 'TimersController@store',
         'as'   => 'api_timers_store_path'
     ]);

     Route::put('/timers/{id}', [
         'uses' => 'TimersController@update',
         'as'   => 'api_timers_update_path'
     ]);

     Route::delete('/timers/{id}', [
         'uses' => 'TimersController@destroy',
         'as'   => 'api_timers_destroy_path'
     ]);

    /*
     * Quotes Routes
     */
     Route::get('/quotes', [
         'uses' => 'QuotesController@index',
         'as'   => 'api_quotes_path'
     ]);

     Route::get('/quotes/random', [
         'uses' => 'QuotesController@random',
         'as'   => 'api_quotes_random_path'
     ]);

     Route::get('/quotes/{id}', [
         'uses'  => 'QuotesController@show',
         'as'    => 'api_quotes_show_path'
     ]);

     Route::post('/quotes', [
         'uses' => 'QuotesController@store',
         'as'   => 'api_quotes_store_path'
     ]);

     Route::put('/quotes/{id}', [
         'uses' => 'QuotesController@update',
         'as'   => 'api_quotes_update_path'
     ]);

     Route::delete('/quotes/{id}', [
         'uses' => 'QuotesController@destroy',
         'as'   => 'api_quotes_destroy_path'
     ]);

    /*
     * Settings Routes
     */
    Route::put('/settings', [
        'uses' => 'SettingsController@update',
        'as'   => 'api_settings_update_path'
    ]);

    Route::put('/settings/named-rankings', [
        'uses' => 'SettingsController@updateNamedRankings',
        'as'   => 'api_settings_named_rankings_update_path'
    ]);

    /*
     * Currency Routes
     */
    Route::get('/currency', [
        'uses'  => 'CurrencyController@index',
        'as'    => 'api_currency_index_path'
    ]);

    Route::post('/currency', [
        'uses'  => 'CurrencyController@addCurrency',
        'as'    => 'api_currency_add_path'
    ]);

    Route::delete('/currency', [
        'uses'  => 'CurrencyController@removeCurrency',
        'as'    => 'api_currency_remove_path'
    ]);

    Route::post('/currency/start-system', [
        'uses'  => 'CurrencyController@startSystem',
        'as'    => 'api_currency_stop_system_path'
    ]);

    Route::post('/currency/stop-system', [
        'uses'  => 'CurrencyController@stopSystem',
        'as'    => 'api_currency_start_system_path'
    ]);

    /*
     * Giveaway Routes
     */
    Route::get('/giveaway/entries', [
        'uses'  => 'GiveawayController@entries',
        'as'    =>  'api_giveaway_entries_path'
    ]);

    Route::post('/giveaway/enter', [
        'uses'  => 'GiveawayController@enter',
        'as'    => 'api_giveaway_enter_path'
    ]);

    Route::post('/giveaway/start', [
        'uses'  => 'GiveawayController@start',
        'as'    => 'api_giveaway_start_path'
    ]);

    Route::post('/giveaway/stop', [
        'uses'  => 'GiveawayController@stop',
        'as'    => 'api_giveaway_stop_path'
    ]);

    Route::post('/giveaway/clear', [
        'uses'  => 'GiveawayController@clear',
        'as'    => 'api_giveaway_clear_path'
    ]);

    Route::get('/giveaway/winner', [
        'uses'  => 'GiveawayController@winner',
        'as'    => 'api_giveaway_winner_path'
    ]);

    /*
     * Bot Routes
     */
    Route::get('/bot/status', [
        'uses'  => 'BotController@status',
        'as'    => 'api_bot_status_path'
    ]);

    Route::post('/bot/join', [
        'uses'  => 'BotController@joinChannel',
        'as'    => 'api_bot_join_channel_path'
    ]);

    Route::post('/bot/leave', [
        'uses'  => 'BotController@leaveChannel',
        'as'    => 'api_bot_leave_channel_path'
    ]);
});

Route::group(['domain' => '{channel}.' . config('app.root_domain'), 'middleware' => ['web']], function () {
    Route::get('/', [
        'uses'  => 'HomeController@index',
        'as' => 'home_path'
    ]);

    /**
     * Commands Routes
     */
    Route::get('/commands', [
        'uses'  => 'CommandsController@index',
        'as'    => 'commands_path'
    ]);

    /**
     * Chat Log Routs
     */
     Route::get('/chat-logs', [
         'middleware' => 'auth',
         'as'   => 'chat_logs_path',
         'uses' => function () {
             return view('chat-logs');
         }
     ]);

    /**
     * Timers Routes
     */
    Route::get('/timers', [
        'uses'  => 'TimersController@index',
        'as'    => 'timers_path'
    ]);

    /**
     * Quotes Routes
     */
    Route::get('/quotes', [
        'uses'  => 'QuotesController@index',
        'as'    => 'quotes_path'
    ]);

    /**
     * Currency Routes
     */
    Route::get('/scoreboard', [
        'uses'  => 'CurrencyController@scoreboard',
        'as'    => 'scoreboard_path'
    ]);

    /**
     * Giveaway Routes
     */
    Route::get('/giveaway', [
        'uses'  => 'GiveawayController@index',
        'as'    => 'giveaway_path'
    ]);

    /**
     * Authentication Routes
     */
    Route::get('/login', [
        'uses'  => 'AuthController@login',
        'as'    => 'login_path'
    ]);

    Route::get('/logout', [
        'uses'  => 'AuthController@logout',
        'as'    => 'logout_path'
    ]);

    Route::post('/pusher/auth', [
        'uses'  => 'AuthController@pusher',
        'as'    => 'pusher_auth_path'
    ]);
});

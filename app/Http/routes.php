<?php

Route::group(['domain' => config('app.auth_domain')], function () {
    Route::get('/login', [
        'uses'  => 'AuthController@loginProxy',
        'as'    => 'login_proxy_path'
    ]);
});

Route::group(['domain' => config('app.api_domain'), 'prefix' => '{channel}', 'namespace' => 'API'], function () {
    Route::get('/viewer', [
        'uses'  => 'ViewerController@getViewer',
        'as'    => 'api_points_path'
    ]);

    Route::get('/vips', [
        'uses'  => 'GeneralController@getVIPs',
        'as'    => 'api_vips_path'
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

    /*
     * Settings Routes
     */
    Route::put('/settings', [
        'uses' => 'SettingsController@update',
        'as'   => 'api_settings_update_path'
    ]);

    /*
     * Currency Routes
     */
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

    Route::post('/giveaway/reset', [
        'uses'  => 'GiveawayController@reset',
        'as'    => 'api_giveaway_reset_path'
    ]);

    Route::get('/giveaway/winner', [
        'uses'  => 'GiveawayController@winner',
        'as'    => 'api_giveaway_winner_path'
    ]);
});

Route::group(['domain' => '{channel}.' . config('app.root_domain'), 'middleware' => ['web']], function () {
    Route::get('/', [
        'uses'  => function(\App\Channel $channel) {
            return redirect()->route('scoreboard_path', [$channel->slug])->with('message', session('message'));
        },
        'as' => 'home_path'
    ]);

    // Route::get('/commands', [
    //     'uses'  => 'CommandsController@index',
    //     'as'    => 'commands_path'
    // ]);

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

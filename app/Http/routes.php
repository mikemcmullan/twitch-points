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

    Route::get('/commands', [
        'uses'  => 'CommandsController@getCommands',
        'as'    => 'api_commands_path'
    ]);

    Route::post('/currency', [
        'uses'  => 'CurrencyController@addCurrency',
        'as'    => 'api_currency_add_path'
    ]);

    Route::delete('/currency', [
        'uses'  => 'CurrencyController@removeCurrency',
        'as'    => 'api_currency_remove_path'
    ]);

    Route::get('/giveaway/enter', [
        'uses'  => 'GiveAwayController@enter',
        'as'    => 'api_giveaway_enter_path'
    ]);

    Route::get('/betting/strings', function() {
        return response()->json([
            "betting_open" => "Betting has been started, the options are: {{ options }}. Min bet {{ min }}, max bet {{ max }}. Instructions: !bet <amount> <option-#>",
            "betting_closed" => "Betting has closed.",
            "betting_winner" => "Option '{{ option }}' is the winner. {{ winners }} have been awarded there 1ups.",
            "bet_was_placed" => "Bet was placed.",
            "no_winners" => "Nobody won.",
            "errors" => [
                "invalid_option" => "{{ user }}, option '{{ option }}' is not valid.",
                "betting_not_open" => "Betting not open.",
                "betting_not_closed" => "Betting not closed.",
                "invalid_bet_size" => "{{ user }}, your bet was either to small or to large.",
                "insufficient_funds" => "{{ user }}, insufficient funds to make bet.",
                "user_already_bet" => "{{ user }} has already bet."
            ]
        ]);
    });

    Route::get('update-caches', function(\Illuminate\Http\Request $request) {
        \Event::fire(new \App\Events\CommandWasUpdated($request->route()->getParameter('channel')));
        \Event::fire(new \App\Events\VIPsWasUpdated($request->route()->getParameter('channel')));
        \Event::fire(new \App\Events\BettingWasUpdated($request->route()->getParameter('channel')));
    });
});


Route::group(['domain' => '{channel}.' . config('app.root_domain'), 'middleware' => ['web']], function () {

    Route::get('/', [
        'uses'  => 'CurrencyController@checkPoints',
        'as'    => 'home_path'
    ]);

    Route::get('/check-points', [
        'uses'  => 'CurrencyController@checkPoints',
        'as'    => 'check_points_path'
    ]);

    Route::get('/system-control', [
        'uses'  => 'CurrencyController@systemControl',
        'as'    => 'system_control_path'
    ]);

    Route::patch('/system-control', [
        'uses'  => 'CurrencyController@startSystem',
        'as'    => 'start_system_path'
    ]);

    Route::get('/scoreboard', [
        'uses'  => 'CurrencyController@scoreboard',
        'as'    => 'scoreboard_path'
    ]);

    Route::get('/login', [
        'uses'  => 'AuthController@login',
        'as'    => 'login_path'
    ]);

    Route::get('/logout', [
        'uses'  => 'AuthController@logout',
        'as'    => 'logout_path'
    ]);

    Route::get('/giveaway', [
        'uses'  => 'GiveAwayController@index',
        'as'    => 'giveaway_path'
    ]);

    Route::post('/giveaway/reset', [
        'uses'  => 'GiveAwayController@reset',
        'as'    => 'giveaway_reset_path'
    ]);

    Route::post('/giveaway/start', [
        'uses'  => 'GiveAwayController@start',
        'as'    => 'giveaway_start_path'
    ]);

    Route::post('/giveaway/stop', [
        'uses'  => 'GiveAwayController@stop',
        'as'    => 'giveaway_stop_path'
    ]);

    Route::post('/pusher/auth', [
        'uses'  => 'AuthController@pusher',
        'as'    => 'pusher_auth_path'
    ]);

    Route::post('/giveaway/winner', [
        'uses'  => 'GiveAwayController@winner',
        'as'    => 'giveaway_winner_path'
    ]);

    Route::post('/giveaway/save-settings', [
        'uses'  => 'GiveAwayController@saveSettings',
        'as'    => 'giveaway_save_settings_path'
    ]);
});

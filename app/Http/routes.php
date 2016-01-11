<?php
use Illuminate\Http\Request;

Route::group(['domain' => env('AUTH_DOMAIN', 'auth.twitch.dev')], function () {
    Route::get('/login', [
        'uses'  => 'AuthController@loginProxy',
        'as'    => 'login_proxy_path'
    ]);
});

Route::group(['domain' => 'api.' . config('app.root_domain'), 'prefix' => '{channel}', 'namespace' => 'API'], function () {
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

<?php

Route::group(['domain' => env('AUTH_DOMAIN', 'auth.twitch.dev')], function () {
    Route::get('/login', [
        'uses'  => 'AuthController@loginProxy',
        'as'    => 'login_proxy_path'
    ]);
});

Route::group(['domain' => '{channel}.' . env('CHANNEL_DOMAIN', 'twitch.dev')], function () {

    Route::get('/', [
        'uses'  => 'PointsController@checkPoints',
        'as'    => 'home_path'
    ]);

    Route::get('/check-points', [
        'uses'  => 'PointsController@checkPoints',
        'as'    => 'check_points_path'
    ]);

    Route::get('/system-control', [
        'uses'  => 'PointsController@systemControl',
        'as'    => 'system_control_path'
    ]);

    Route::patch('/system-control', [
        'uses'  => 'PointsController@startSystem',
        'as'    => 'start_system_path'
    ]);

    Route::get('/scoreboard', [
        'uses'  => 'PointsController@scoreboard',
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

    Route::group(['prefix' => 'api', 'namespace' => 'API'], function () {
        Route::get('/viewer', [
            'uses'  => 'ViewerController@getViewer',
            'as'    => 'api_points_path'
        ]);

        Route::get('/vips', [
            'uses'  => 'GeneralController@getVIPs',
            'as'    => 'api_vips_path'
        ]);

        Route::post('/points', [
            'uses'  => 'PointsController@addPoints',
            'as'    => 'api_points_add_path'
        ]);

        Route::delete('/points', [
            'uses'  => 'PointsController@removePoints',
            'as'    => 'api_points_remove_path'
        ]);

        Route::get('/giveaway/enter', [
            'uses'  => 'GiveAwayController@enter',
            'as'    => 'api_giveaway_enter_path'
        ]);
    });
});

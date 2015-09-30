<?php

Route::group(['domain' => env('AUTH_DOMAIN', 'auth.twitch.dev')], function () {
    get('/login', [
        'uses'  => 'AuthController@loginProxy',
        'as'    => 'login_proxy_path'
    ]);
});

Route::group(['domain' => '{channel}.' . env('CHANNEL_DOMAIN', 'twitch.dev')], function () {

    get('/', [
        'uses'  => 'PointsController@checkPoints',
        'as'    => 'home_path'
    ]);
    
    get('/check-points', [
        'uses'  => 'PointsController@checkPoints',
        'as'    => 'check_points_path'
    ]);
    
    get('/system-control', [
        'uses'  => 'PointsController@systemControl',
        'as'    => 'system_control_path'
    ]);
    
    patch('/system-control', [
        'uses'  => 'PointsController@startSystem',
        'as'    => 'start_system_path'
    ]);
        
    get('/scoreboard', [
        'uses'  => 'PointsController@scoreboard',
        'as'    => 'scoreboard_path'
    ]);

    get('/login', [
        'uses'  => 'AuthController@login',
        'as'    => 'login_path'
    ]);
    
    get('/logout', [
        'uses'  => 'AuthController@logout',
        'as'    => 'logout_path'
    ]);

    get('/giveaway', [
        'uses'  => 'GiveAwayController@index',
        'as'    => 'giveaway_path'
    ]);

    post('/giveaway/reset', [
        'uses'  => 'GiveAwayController@reset',
        'as'    => 'giveaway_reset_path'
    ]);

    post('/giveaway/start', [
        'uses'  => 'GiveAwayController@start',
        'as'    => 'giveaway_start_path'
    ]);

    post('/giveaway/stop', [
        'uses'  => 'GiveAwayController@stop',
        'as'    => 'giveaway_stop_path'
    ]);

    post('/pusher/auth', [
        'uses'  => 'AuthController@pusher',
        'as'    => 'pusher_auth_path'
    ]);

    post('/giveaway/winner', [
        'uses'  => 'GiveAwayController@winner',
        'as'    => 'giveaway_winner_path'
    ]);
        
    Route::group(['prefix' => 'api', 'namespace' => 'API'], function () {
        get('/viewer', [
            'uses'  => 'ViewerController@getViewer',
            'as'    => 'api_points_path'
        ]);
    
        post('/points', [
            'uses'  => 'PointsController@addPoints',
            'as'    => 'api_points_add_path'
        ]);
    
        delete('/points', [
            'uses'  => 'PointsController@removePoints',
            'as'    => 'api_points_remove_path'
        ]);

        get('/giveaway/enter', [
            'uses'  => 'GiveAwayController@enter',
            'as'    => 'api_giveaway_enter_path'
        ]);
    });
});

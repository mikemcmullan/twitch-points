<?php

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

Route::get('/bot-control', [
    'uses'  => 'BotController@botControl',
    'as'    => 'bot_control_path'
]);

Route::get('/bot-control/log', [
    'uses'  => 'BotController@viewLog',
    'as'    => 'bot_view_log_path'
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

Route::group(['prefix' => 'api', 'namespace' => 'API'], function()
{
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

    get('/bot/log', [
        'uses'  => 'BotController@getLog',
        'as'    => 'api_bot_log_path'
    ]);

    get('/bot/join', [
        'uses'  => 'BotController@joinChannel',
        'as'    => 'api_bot_join_channel_path'
    ]);

    get('/bot/leave', [
        'uses'  => 'BotController@leaveChannel',
        'as'    => 'api_bot_leave_channel_path'
    ]);

    get('/bot/start', [
        'uses'  => 'BotController@startBot',
        'as'    => 'api_bot_start_path'
    ]);

    get('/bot/stop', [
        'uses'  => 'BotController@stopBot',
        'as'    => 'api_bot_stop_path'
    ]);

    get('/bot/status', [
        'uses'  => 'BotController@getStatus',
        'as'    => 'api_bot_status_path'
    ]);

    get('/bot/token', [
        'uses'  => 'BotController@getToken',
        'as'    => 'api_bot_token_path'
    ]);

    get('/bot/validate-token', [
        'uses'  => 'BotController@validateToken',
        'as'    => 'api_bot_validate_token_path'
    ]);
});
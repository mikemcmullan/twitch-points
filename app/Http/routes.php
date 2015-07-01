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

Route::get('/api/viewer', [
    'uses'  => 'ApiController@getViewer',
    'as'    => 'api_points_path'
]);

Route::post('/api/points', [
    'uses'  => 'ApiController@addPoints',
    'as'    => 'api_points_add_path'
]);

Route::delete('/api/points', [
    'uses'  => 'ApiController@removePoints',
    'as'    => 'api_points_remove_path'
]);
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

Route::get('/api/points', [
    'uses'  => 'ApiController@points',
    'as'    => 'api_points_path'
]);
<?php

Route::get('/', [
    'uses'  => 'HomeController@index',
    'as' => 'home_path'
]);

/**
 * Sound Effects Routes
 */
Route::get('/sound-effects', [
    'uses'  => function() {
        return view('sound-effects');
    },
    'as'    => 'sound_effects_path'
]);

/**
 * Sound Effects Routes
 */
Route::get('/queue', [
    'uses'  => 'QueueController@index',
    'as'    => 'queue_path'
]);

/**
 * Commands Routes
 */
Route::get('/commands', [
    'uses'  => 'CommandsController@index',
    'as'    => 'commands_path'
]);

/**
 * Chat Log Routes
 */
Route::get('/chat-logs', [
    'as'   => 'chat_logs_path',
    'uses' => 'ChatLogsController@index'
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

Route::get('/login/callback', [
    'uses'  => 'AuthController@loginCallback',
    'as'    => 'login_callback_path'
]);

Route::get('/logout', [
    'uses'  => 'AuthController@logout',
    'as'    => 'logout_path'
]);

Route::post('/pusher/auth', [
    'uses'  => 'AuthController@pusher',
    'as'    => 'pusher_auth_path'
]);
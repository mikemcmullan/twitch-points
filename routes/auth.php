<?php

Route::get('/login', [
    'uses'  => 'AuthController@loginProxy',
    'as'    => 'login_proxy_path'
]);
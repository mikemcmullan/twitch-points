<?php

use App\Services\UpdateDBChatUsers;

get('/', function(\App\Repositories\ChatUsers\ChatUserRepository $repo, \App\Services\TwitchApi $twitchApi)
{
    $channel = 'xxleroy1605xx';

    $timer = new \PHPBenchTime\Timer();
    $timer->start();

    $user = $repo->user($channel, 'glite81');

    var_dump($user);

    dd($timer->end());
});
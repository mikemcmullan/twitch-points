<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\TwitchApi;
use Redis;

class ProcessTwitchToFetchList implements ShouldQueue
{
    // use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    protected function getList()
    {
        return collect(Redis::smembers('twitch:toFetch'));
    }

    protected function deleteFromList($username)
    {
        return (bool) Redis::srem('twitch:toFetch', $username);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TwitchApi $twitchApi)
    {
        $list = $this->getList();

        $list->chunk(100)
            ->each(function ($usernames) use ($twitchApi, $list) {
            $response = $twitchApi->getUsersByUsername($usernames->toArray());

            foreach ($response as $user) {
                addUserToRedis([
                    'id'            => $user['id'],
                    'username'      => $user['login'],
                    'display_name'  => $user['display_name']
                ]);
            }

            foreach ($usernames as $username) {
                $this->deleteFromList($username);
            }

            if ($list->count() > 100) {
                sleep(2);
            }
        });
    }
}

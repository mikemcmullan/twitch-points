<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Chatter;
use App\Services\TwitchApi;
use DB;

class ResolveServiceIdsFromTwitch implements ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TwitchApi $twitchApi)
    {
        $chatters = Chatter::whereNull('service_id')->get();

        $chatters->chunk(100)->each(function ($chunk) use ($twitchApi) {
            $response = $twitchApi->getUsersByUsername($chunk->pluck('username')->toArray());

            DB::transaction(function () use ($response) {
                foreach ($response as $user) {
                    DB::table('chatters')
                        ->where('username', $user['login'])
                        ->update(['service_id' => $user['id']]);
                }
            });

            sleep(2);
        });
    }
}

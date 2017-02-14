<?php

namespace App\Jobs;

use App\ChatLogs;
use Cache;

class UpdateDisplayNameCache extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $names = ChatLogs::findUniqueUsernames();

        foreach ($names as $name) {
            Cache::forever("displayNameMap:{$name->username}", $name->display_name ?? $name->username);
        }
    }
}

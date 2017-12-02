<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\ResolveServiceIdsFromTwitch as ResolveServiceIdsFromTwitchJob;

class ResolveServiceIdsFromTwitch extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:resolve-service-ids-from-twitch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find chatters with a null service_id and get the id from the twitch api.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dispatch(new ResolveServiceIdsFromTwitchJob());
    }
}

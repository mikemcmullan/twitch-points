<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\ProcessTwitchToFetchList as ProcessTwitchToFetchListJob;

class ProcessTwitchToFetchList extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:process-twitch-to-fetch-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate through toFetch list retrieving user information from the twitch api.';

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
        $this->dispatch(new ProcessTwitchToFetchListJob());
    }
}

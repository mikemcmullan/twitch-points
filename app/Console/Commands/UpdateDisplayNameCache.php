<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\UpdateDisplayNameCache as UpdateDisplayNameCacheJob;

class UpdateDisplayNameCache extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:update-display-name-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all the unque usernames in chat logs and cache their display names.';

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
        return $this->dispatch(new UpdateDisplayNameCacheJob());
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\DownloadChatListJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Currency\CurrencyChannels;

class UpdatePoints extends Command
{
    use DispatchesJobs, CurrencyChannels;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'points:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update chat list for a channel.';

    /**
     * Create a new command instance.
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
    public function fire()
    {
        $startTime = microtime(true);

        try {
            foreach ($this->getActiveCurrencyChannels() as $channel) {
                $this->dispatch(new DownloadChatListJob($channel));
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $end = microtime(true) - $startTime;
        \Log::info(sprintf('Updated Points in %s seconds', $end));
        $this->info(sprintf('Updated Points in %s seconds', $end));
    }
}

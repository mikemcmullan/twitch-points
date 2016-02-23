<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Timers\Manager as TimerManager;

class RunTimer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:run-timer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for then run any bot timers.';

    /**
     * @var TimeManager
     */
     private $timerManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TimerManager $timerManager)
    {
        parent::__construct();
        $this->timerManager = $timerManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('Finding and excuting timers.');
        \Event::listen(\App\Events\TimerWasExecuted::class, function ($timer) {
            \Log::info(sprintf('Excecuting timer %s.', $timer->timer->name), ['channel' => $timer->timer->channel->name]);
        });
        $this->timerManager->execute(\Carbon\Carbon::now());

    }
}

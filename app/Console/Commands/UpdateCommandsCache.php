<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\Commands\CommandsWereUpdated;

class UpdateCommandsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:update-commands-cache {channel : The channel to update the command cache for.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the commands cache.';

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
        $channelStr = $this->argument('channel');
        $channel = \App\Channel::findByName($channelStr);

        if (! $channel) {
            return $this->info("Channel '{$channelStr}' was not found.");
        }

        app(\App\Support\SetupBotCommands::class)->setup($channel);

        $this->info("Command cache has been updated for channel '{$channel->name}'.");
        event(new CommandsWereUpdated($channel));
    }
}

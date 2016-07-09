<?php

namespace App\Listeners\Commands;

use App\Events\Commands\CommandsWereUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Redis\Database;
use App\Command;

class UpdateCommandsBotCache
{
    /**
     * @var string
     */
    private $commandsKey = '#%s:commands';

    /**
     * @var string
     */
    private $commandKey = '#%s:commands:%d';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Handle the event.
     *
     * @param  AppEventsCommandWasUpdated  $event
     * @return void
     */
    public function handle(CommandsWereUpdated $event)
    {
        $commands = Command::allForChannel($event->channel, false);

        $commands = $commands->map(function ($command) use ($event) {
            // In the bot I have switched to using module instead of file.
            if (! isset($command->file) || ($command->file === '' || $command->file === null)) {
                $command->module = 'Simple';
            } else {
                $command->module = $command->file;
            }

            return array_only($command->toArray(), ['id', 'cool_down', 'command', 'pattern', 'usage', 'level', 'type', 'response', 'module']);
        });

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
    }
}

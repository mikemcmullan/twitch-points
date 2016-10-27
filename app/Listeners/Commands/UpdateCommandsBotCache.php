<?php

namespace App\Listeners\Commands;

use App\BotCommands\Manager as CommandManager;
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
     * @var CommandManager
     */
    protected $commandManager;

    /**
     * Create the event listener.
     *
     * @param Database $redis
     * @param CommandManager $commandManager
     */
    public function __construct(Database $redis, CommandManager $commandManager)
    {
        $this->redis = $redis;
        $this->commandManager = $commandManager;
    }

    /**
     * Handle the event.
     *
     * @param CommandsWereUpdated $event
     */
    public function handle(CommandsWereUpdated $event)
    {
        $system = $this->commandManager->allSystem($event->channel);
        $custom = $this->commandManager->allCustom($event->channel);
        $commands = $system->merge($custom);

        $commands = $commands->map(function ($command) use ($event) {
            // In the bot I have switched to using module instead of file.
            if (isset($command['file']) && $command['file'] === '') {
                $command['module'] = 'Simple';
            } else if (isset($command['file']) && $command['file'] !== ''){
                $command['module'] = $command['file'];
            }

            if (is_object($command)) {
                $command = $command->toArray();
            }

            return array_only($command, ['id', 'cool_down', 'global_cool_down', 'command', 'usage', 'level', 'type', 'response', 'module']);
        });

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
    }
}

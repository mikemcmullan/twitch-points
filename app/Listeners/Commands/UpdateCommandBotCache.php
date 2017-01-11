<?php

namespace App\Listeners\Commands;

use App\Events\Commands\CommandWasUpdated;
use Illuminate\Redis\Database;

class UpdateCommandBotCache
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
     * @param Database $redis
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Handle the event.
     *
     * @param CommandWasUpdated $event
     */
    public function handle(CommandWasUpdated $event)
    {
        $commands = collect(json_decode($this->redis->get(sprintf($this->commandsKey, $event->channel->name)), true));
        $existing = $commands->where('id', $event->command['id']);

        if (is_object($event->command)) {
            $event->command = $event->command->toArray();
        }

        $command = array_only($event->command, [
            'id', 'cool_down', 'global_cool_down', 'command', 'usage', 'level', 'type', 'response', 'file', 'module'
        ]);

        if ($existing->isEmpty()) {
            $commands->push($command);
        } else {
            $key = $existing->keys()->first();
            $commands->splice($key, 1, [$command]);
        }

        // In the bot I have switched to using module instead of file.
        $commands = $commands->map(function ($command, $key) {
            if (isset($command['module'])) {
                return $command;
            }

            // In the bot I have switched to using module instead of file.
            if (isset($command['file']) && $command['file'] === '' || ! isset($command['file'])) {
                $command['module'] = 'Simple';
            } else if (isset($command['file']) && $command['file'] !== ''){
                $command['module'] = $command['file'];
            }

            unset($command['file']);

            return $command;
        });

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
    }
}

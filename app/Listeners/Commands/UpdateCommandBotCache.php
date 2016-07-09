<?php

namespace App\Listeners\Commands;

use App\Events\Commands\CommandWasUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
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
    public function handle(CommandWasUpdated $event)
    {
        $commands = collect(json_decode($this->redis->get(sprintf($this->commandsKey, $event->channel->name))));
        $existing = $commands->where('id', $event->command->id);

        $command = array_only($event->command->toArray(), [
            'id', 'cool_down', 'command', 'pattern', 'usage', 'level', 'type', 'response'
        ]);

        if ($existing->isEmpty()) {
            $commands->push($command);
        } else {
            $key = $existing->keys()->first();
            $commands->splice($key, 1, [$command]);
        }

        // In the bot I have switched to using module instead of file.
        $commands = $commands->map(function ($command, $key) {
            if (! isset($command['file']) || ($command['file'] === '' || $command['file'] === null)) {
                $command['module'] = 'Simple';
            } else {
                $command['module'] = $command['file'];
            }

            unset($command['file']);

            return $command;
        });

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
    }
}

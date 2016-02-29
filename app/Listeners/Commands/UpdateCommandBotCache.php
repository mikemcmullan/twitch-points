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

        if ($existing->isEmpty()) {
            $commands->push([
                'id' => $event->command->id,
                'pattern' => $event->command->pattern,
                'level' => $event->command->level,
                'cool_down' => $event->command->cool_down
            ]);
        } else {
            $key = $existing->keys()->first();

            $commands->splice($key, 1, [[
                'id' => $event->command->id,
                'pattern' => $event->command->pattern,
                'level' => $event->command->level,
                'cool_down' => $event->command->cool_down
            ]]);
        }

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
        $this->redis->set(sprintf($this->commandKey, $event->channel->name, $event->command->id), $event->command);
    }
}

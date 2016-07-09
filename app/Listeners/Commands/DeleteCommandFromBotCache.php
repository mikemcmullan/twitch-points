<?php

namespace App\Listeners\Commands;

use App\Events\Commands\CommandWasDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Redis\Database;

class DeleteCommandFromBotCache
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
    public function handle(CommandWasDeleted $event)
    {
        $commands = collect(json_decode($this->redis->get(sprintf($this->commandsKey, $event->channel->name))));

        $existing = $commands->where('id', $event->command->id);

        if (! $existing->isEmpty()) {
            $key = $existing->keys()->first();

            $commands->splice($key, 1);
        }

        $this->redis->set(sprintf($this->commandsKey, $event->channel->name), $commands);
    }
}

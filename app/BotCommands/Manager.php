<?php

namespace App\BotCommands;

use App\Command;
use App\Channel;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;

class Manager
{
    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Get all commands for a channel.
     *
     * @return Collection
     */
    public function all(Channel $channel)
    {
        return Command::where('channel_id', $channel->id)->get();
    }

    /**
     * Get a command.
     *
     * @param $id
     * @return Command
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(Channel $channel, $id)
    {
        return Command::where('channel_id', $channel->id)->findOrFail($id);
    }

    /**
     * Create a new command.
     *
     * @param Channel $channel
     * @param array $data
     *
     * @return Command
     */
    public function create(Channel $channel, array $data)
    {
        $data['pattern'] = $this->makePattern($data['command']);
        $data['type']    = 'custom';
        $data['level']   = is_null($data['level']) ? 'everyone' : $data['level'];
        $data['usage']   = array_get($data, 'usage', '');
        $data['description'] = array_get($data, 'description', '');

        $created = $channel->commands()->create($data);

        $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $created));

        return $created;
    }

    /**
     * Update a command.
     *
     * @param Channel $channel
     * @param int $id
     * @param array $data
     *
     * @return Command
     */
    public function update(Channel $channel, $id, $data)
    {
        $command = $this->get($channel, $id);

        if (array_get($data, 'command')) {
            $command->command = array_get($data, 'command');
            $command->pattern = $this->makePattern(array_get($data, 'command'));
        }

        if (array_get($data, 'level')) {
            $command->level = array_get($data, 'level');
        }

        if (array_get($data, 'response')) {
            $command->response = array_get($data, 'response');
        }

        if (array_get($data, 'usage')) {
            $command->usage = array_get($data, 'usage');
        }

        if (array_get($data, 'description')) {
            $command->description = array_get($data, 'description');
        }

        $command->save();

        $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $command));

        return $command;
    }

    /*
     * Delete a command.
     *
     * @param $id
     * @return int
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(Channel $channel, $id)
    {
        $command = $this->get($channel, $id);

        $this->events->fire(new \App\Events\Commands\CommandWasDeleted($channel, $command));

        return $command->delete();
    }

    /**
     * Make pattern.
     *
     * @param string $command
     * @return string
     */
    private function makePattern($command)
    {
        if (substr($command, 0, 6) === 'regex:') {
            return substr($command, 6);
        } else {
            return '^' . preg_quote(trim($command)) . '(.*)$';
        }
    }
}

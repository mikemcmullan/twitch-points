<?php

namespace App\BotCommands;

use App\Command;
use App\Channel;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use App\Support\BasicManager;
use App\Contracts\BasicManager as BasicManagerInterface;
use Illuminate\Contracts\Validation\ValidationException;

class Manager extends BasicManager implements BasicManagerInterface
{
    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events, Command $model)
    {
        $this->events = $events;
        $this->model = $model;
    }

    /**
     * Return an instance of the model we will be working with.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get all commands for a channel.
     *
     * @return Collection
     */
    public function all(Channel $channel)
    {
        return Command::where('channel_id', $channel->id)->orderBy('order')->get();
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
        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        // Validate the request
        $validator = \Validator::make($data, [
            'command'       => 'required|max:80',
            'level'         => 'required|in:everyone,mod,admin,owner',
            'response'      => 'required|max:400',
            'disabled'      => 'sometimes|required|boolean',
            'usage'         => 'sometimes|required|max:50',
            'description'   => 'sometimes|required|max:400'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data['pattern'] = $this->makePattern($data['command']);
        $data['type']    = 'custom';

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
    public function update(Channel $channel, $id, array $data)
    {
        $command = $this->get($channel, $id);

        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        // Validate the request
        $validator = \Validator::make($data, [
            'command'       => 'sometimes|required|max:80',
            'level'         => 'sometimes|required|in:everyone,mod,admin,owner',
            'response'      => 'sometimes|required|max:400',
            'disabled'      => 'sometimes|required|boolean',
            'usage'         => 'sometimes|required|max:50',
            'description'   => 'sometimes|required|max:400'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (array_get($data, 'command')) {
            $data['pattern'] = $this->makePattern($data['command']);
        }

        $command->fill($data);
        $command->save();

        if ($command->disabled) {
            $this->events->fire(new \App\Events\Commands\CommandWasDeleted($channel, $command));
        } else {
            $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $command));
        }

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

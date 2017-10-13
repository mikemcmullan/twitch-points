<?php

namespace App\BotCommands;

use App\Command;
use App\Channel;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use App\Support\BasicManager;
use App\Contracts\BasicManager as BasicManagerInterface;
use App\SystemCommandOverrides;
use Illuminate\Validation\ValidationException;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Validation\Rule;

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
     * @var ConfigRepository
     */
    protected $config;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events, ConfigRepository $config, Command $model)
    {
        $this->events = $events;
        $this->model = $model;
        $this->config = $config;
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
     * Get all custom commands for a channel.
     *
     * @param Channel $channel
     * @param string $orderBy
     * @param string $orderDirection
     * @param null|bool $disabled
     *
     * @return Collection
     */
    public function allCustom(Channel $channel, $orderBy = 'order', $orderDirection = 'DESC', $disabled = null)
    {
        $where = [
            'channel_id' => $channel->id
        ];

        if ($disabled !== null) {
            $where['disabled'] = (bool) $disabled;
        }

        return Command::where($where)->orderBy($orderBy, $orderDirection)->get()->map(function ($command) use ($channel) {
            $command['count'] = $this->getCommandCount($channel, $command->id);

            return $command;
        });
    }

    /**
     * Get all system commands for a channel.
     *
     * @param Channel $channel
     * @param null|bool $disabled
     *
     * @return Collection
     */
    public function allSystem(Channel $channel, $disabled = null)
    {
        $commands = collect();

        collect(config('commands.system', []))->each(function ($comms, $groupKey) use (&$commands, $disabled, $channel) {
            foreach ($comms as $key => $command) {

                if ($command['disabled'] === true && $disabled == '0') {
                    continue;
                }

                $command['count'] = $this->getCommandCount($channel, $command['id']);

                $commands->push($command);
            }
        });

        return $commands;
    }

    /**
     * Create a new command.
     *
     * @param Channel $channel
     * @param array $data
     * @return Command
     * @throws ValidationException
     */
    public function create(Channel $channel, array $data)
    {
        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $data = array_map(function ($item) {
            return is_bool($item) ? $item : trim($item);
        }, $data);

        // Validate the request
        $validator = \Validator::make($data, [
            'command'       => [
                'required',
                'max:40',
                Rule::unique('commands')->where(function ($query) use ($channel) {
                    $query->where('channel_id', $channel->id);
                })
            ],
            'level'         => 'required|in:everyone,sub,mod,admin,owner',
            'response'      => 'required|max:400',
            'disabled'      => 'sometimes|required|boolean',
            'usage'         => 'sometimes|required|max:50',
            'description'   => 'sometimes|required|max:400',
            'cool_down'     => 'required|numeric_size_between:0,300',
            'count'         => 'sometimes|required|numeric_size_between:0,1000000'
        ], [
            'command.unique'=> "Command '{$data['command']}' already exists."
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $count = $data['count'];
        unset($data['count']);

        $data['type']    = 'custom';

        $created = $channel->commands()->create($data);
        $created->count = $count;
        $this->updateCommandCount($channel, $created->id, $count);

        if (! $created->disabled) {
            $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $created));
        }

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
        if ($this->isSystemCommand($channel, $id)) {
            return $this->updateSystem($channel, $id, $data);
        }

        $command = $this->get($channel, $id);

        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $data = array_map(function ($item) {
            return is_bool($item) ? $item : trim($item);
        }, $data);

        // Validate the request
        $validator = \Validator::make($data, [
            'command'       => [
                'sometimes',
                'required',
                'max:40',
                Rule::unique('commands')->where(function ($query) use ($channel) {
                    $query->where('channel_id', $channel->id);
                })->ignore($id)
            ],
            'level'         => 'sometimes|required|in:everyone,sub,mod,admin,owner',
            'response'      => 'sometimes|required|max:400',
            'disabled'      => 'sometimes|required|boolean',
            'usage'         => 'sometimes|required|max:50',
            'description'   => 'sometimes|required|max:400',
            'cool_down'     => 'sometimes|required|numeric_size_between:0,300',
            'count'         => 'sometimes|required|numeric_size_between:0,1000000'
        ], [
            'command.unique'=> "Command '" . (isset($data['command']) ? $data['command'] : $command['command']) . "' already exists."
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $count = $data['count'];
        unset($data['count']);

        $this->updateCommandCount($channel, $id, $count);

        $command->fill($data);
        $command->save();

        if ($command->disabled) {
            $this->events->fire(new \App\Events\Commands\CommandWasDeleted($channel, $command));
        } else {
            $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $command));
        }

        $command->count = $count;

        return $command;
    }

    public function updateSystem(Channel $channel, $id, array $data)
    {
        $command = $this->config->get('commands.system.' . $id);

        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $data = array_map(function ($item) {
            return is_bool($item) ? $item : trim($item);
        }, $data);

        // Validate the request
        $validator = \Validator::make($data, [
            'command'       => 'sometimes|required|max:80',
            'level'         => 'sometimes|required|in:everyone,sub,mod,admin,owner',
            'response'      => 'sometimes|required|max:400',
            'disabled'      => 'sometimes|required|boolean',
            'usage'         => 'sometimes|required|max:50',
            'description'   => 'sometimes|required|max:400',
            'cool_down'     => 'sometimes|required|numeric_size_between:0,300'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $commandOverrides = SystemCommandOverrides::where('channel_id', $channel->id)
            ->where('name', 'LIKE', $id . '%')
            ->get();

        // dd($commandOverrides);

        \DB::beginTransaction();

        foreach ($data as $attr => $value) {
            $overrideExists = $commandOverrides->where('name', $id . '.' . $attr);

            if ($command[$attr] === $value) {
                continue;
            }

            $this->config->set('commands.system.' . $id . '.' . $attr, $value);

            if (! $overrideExists->isEmpty()) {
                $overrideExists->first()->update(['value' => $value]);
            } else {
                SystemCommandOverrides::create([
                    'channel_id' => $channel->id,
                    'name' => $id . '.' . $attr,
                    'value' => $value
                ]);
            }
        }

        \DB::commit();

        $command = $this->config->get('commands.system.' . $id);

        if ($command['disabled']) {
            $this->events->fire(new \App\Events\Commands\CommandWasDeleted($channel, $command));
        } else {
            $this->events->fire(new \App\Events\Commands\CommandWasUpdated($channel, $command));
        }

        return $command;
    }

    protected function isSystemCommand($channel, $id)
    {
        return $this->config->get('commands.system.' . $id) !== null;
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

        $this->deleteCommandCount($channel, $id);

        $this->events->fire(new \App\Events\Commands\CommandWasDeleted($channel, $command));

        return $command->delete();
    }

    /**
     * Get how many times a command was used.
     *
     * @param  Channel      $channel
     * @param  int|string   $commandId
     * @return int
     */
    protected function getCommandCount(Channel $channel, $commandId)
    {
        return (int) app('redis')->get("#{$channel->name}:count:{$commandId}");
    }

    /**
     * Update how many times a command has been used.
     *
     * @param  Channel      $channel
     * @param  int|string   $commandId
     * @param  int|string   $newCount
     * @return int
     */
    protected function updateCommandCount(Channel $channel, $commandId, $newCount)
    {
        return app('redis')->set("#{$channel->name}:count:{$commandId}", (int) $newCount);
    }

    /**
     * Delete how many times a command has been used.
     *
     * @param  Channel      $channel
     * @param  int|string   $commandId
     * @return int
     */
    protected function deleteCommandCount(Channel $channel, $commandId)
    {
        return app('redis')->del("#{$channel->name}:count:{$commandId}");
    }
}

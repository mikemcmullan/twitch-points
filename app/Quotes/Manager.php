<?php

namespace App\Quotes;

use App\Channel;
use App\Support\BasicManager;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\ValidationException;
use App\Contracts\BasicManager as BasicManagerInterface;

class Manager extends BasicManager implements BasicManagerInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @param Dispatcher $events
     */
    public function __construct(Quote $model)
    {
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

    public function getRandom(Channel $channel)
    {
        return $this->model->where('channel_id', $channel->id)
            ->orderByRaw('RAND()')
            ->take(1)
            ->first();
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

        $validator = app(Factory::class)->make($data, [
            'text'     => 'required|max:500'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data['id'] = $this->generateId($channel);

        return $channel->quotes()->create($data);
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
        $timer = $this->get($channel, $id);

        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $validator = app(Factory::class)->make($data, [
            'text'     => 'required|max:500'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $timer->fill($data);
        $timer->save();

        return $timer;
    }

    /**
     * Generate a unique Id for a quote.
     *
     * @param Channel $channel
     *
     * @return int
     */
    protected function generateId(Channel $channel)
    {
        $newId = intval($channel->getSetting('quote.current_id')) + 1;

        $channel->setSetting('quote.current_id', $newId);

        return $newId;
    }
}

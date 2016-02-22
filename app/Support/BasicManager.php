<?php

namespace App\Support;

use App\Channel;

abstract class BasicManager
{
    /**
     * Get all items for a channel.
     *
     * @return Collection
     */
    public function all(Channel $channel)
    {
        return $this->getModel()->where('channel_id', $channel->id)->get();
    }

    /**
     * Get a item.
     *
     * @param $id
     * @return Command
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(Channel $channel, $id)
    {
        return $this->getModel()->where('channel_id', $channel->id)->findOrFail($id);
    }

    /*
     * Delete a item.
     *
     * @param $id
     * @return int
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(Channel $channel, $id)
    {
        $timer = $this->get($channel, $id);

        return $timer->delete();
    }
}

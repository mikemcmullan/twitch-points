<?php

namespace App\Contracts;

use App\Channel;

interface BasicManager {

    /**
     * Return an instance of the model we will be working with.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel();

    /**
     * Get all items for a channel.
     *
     * @return Collection
     */
    public function all(Channel $channel);

    /**
     * Get a item.
     *
     * @param $id
     * @return Command
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(Channel $channel, $id);

    /**
     * Create an item.
     *
     * @param Channel $channel
     * @param array $data
     *
     * @return Model
     */
    public function create(Channel $channel, array $data);

    /**
     * Update an item.
     *
     * @param Channel $channel
     * @param int $id
     * @param array $data
     *
     * @return Model
     */
    public function update(Channel $channel, $id, array $data);


    /*
     * Delete a item.
     *
     * @param $id
     * @return int
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(Channel $channel, $id);

}

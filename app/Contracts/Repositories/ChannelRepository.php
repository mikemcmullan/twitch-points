<?php

namespace App\Contracts\Repositories;

use App\Channel;

interface ChannelRepository
{
    /**
     * Get all channels.
     */
    public function all();

    /**
     * Find a user by their name or create a new user.
     *
     * @param $name
     * @param array $data
     * @return static
     */
    public function findByNameOrCreate($name, array $data = []);

    /**
     * Find a user by their name.
     *
     * @param $name
     * @return mixed
     */
    public function findByName($name);
    
    /**
     * Find a user by their slug.
     *
     * @param $slug
     * @return mixed
     */
    public function findBySlug($slug);

    /**
     * Update a user.
     *
     * @param Channel $channel
     * @return bool
     */
    public function update(Channel $channel);
}

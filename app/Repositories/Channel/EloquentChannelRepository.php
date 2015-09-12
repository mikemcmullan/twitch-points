<?php

namespace App\Repositories\Channel;

use App\Contracts\Repositories\TrackSessionRepository;
use App\Contracts\Repositories\ChannelRepository;
use App\Channel;

class EloquentChannelRepository implements ChannelRepository
{

    /**
     * @var User
     */
    private $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get all channels.
     */
    public function all()
    {
        return $this->channel->all();
    }

    /**
     * Find a user by their name or create a new user.
     *
     * @param $name
     * @param array $data
     * @return static
     */
    public function findByNameOrCreate($name, array $data = [])
    {
        if ($user = $this->findByName($name))
        {
            return $user;
        }

        return $this->channel->create([
            'name'          => $name,
            'email'         => array_get($data, 'email'),
            'logo'          => array_get($data, 'logo'),
            'access_token'  => array_get($data, 'access_token')
        ]);
    }

    /**
     * Find a user by their name.
     *
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return $this->channel->where('name', '=', $name)->first();
    }
    
    /**
	 * Find a user by their slug.
	 *
	 * @param $slug
	 * @return mixed
	 */
	public function findBySlug($slug)
	{
		return $this->channel->where('slug', '=', $slug)->first();
	}

    /**
     * Update a user.
     *
     * @param Channel $channel
     * @return bool
     */
    public function update(Channel $channel)
    {
        return $channel->save();
    }
}
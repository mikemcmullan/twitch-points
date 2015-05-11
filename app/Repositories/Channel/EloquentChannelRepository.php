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
     * @var TrackPointsSession
     */
    private $pointsSession;

    /**
     * @param Channel $channel
     * @param TrackSessionRepository $pointsSession
     */
    public function __construct(Channel $channel, TrackSessionRepository $pointsSession)
    {
        $this->pointsSession = $pointsSession;
        $this->channel = $channel;
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
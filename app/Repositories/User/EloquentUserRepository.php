<?php

namespace App\Repositories\User;

use App\Contracts\Repositories\TrackSessionRepository;
use App\Contracts\Repositories\UserRepository;
use App\User;

class EloquentUserRepository implements UserRepository
{

    /**
     * @var User
     */
    private $user;
    /**
     * @var TrackPointsSession
     */
    private $pointsSession;

    /**
     * @param User $user
     * @param TrackSessionRepository $pointsSession
     */
    public function __construct(User $user, TrackSessionRepository $pointsSession)
    {
        $this->pointsSession = $pointsSession;
        $this->user = $user;
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

        return $this->user->create([
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
        return $this->user->where('name', '=', $name)->first();
    }

    /**
     * Update a user.
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user)
    {
        return $user->save();
    }
}
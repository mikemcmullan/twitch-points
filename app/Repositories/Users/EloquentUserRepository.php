<?php

namespace App\Repositories\Users;

use App\Repositories\TrackPointsSessions\TrackPointsSession;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class EloquentUserRepository extends EloquentUserProvider implements UserRepository, UserProvider
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
     * @param HasherContract $hasher
     * @param TrackPointsSession $pointsSession
     */
    public function __construct(User $user, HasherContract $hasher, TrackPointsSession $pointsSession)
    {
        parent::__construct($hasher, $user);

        $this->user = $user;
        $this->pointsSession = $pointsSession;
    }

    /**
     * Used by EloquentUserProvider
     *
     * @return User
     */
    public function createModel()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveById($identifier)
    {
        return $this->user->with(['trackPoints' => function($query)
        {
            $query->where('complete', false);
        }])->find($identifier);
    }

    /**
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
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return $this->user->where('name', '=', $name)->first();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function update(User $user)
    {
        return $user->save();
    }

    /**
     * Create a new points tracking session.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createTrackPointsSession(User $user)
    {
        return $this->pointsSession->create($user['id']);
    }

    /**
     * End the current point tracking session.
     *
     * @param User $user
     * @return mixed
     */
    public function endTrackPointsSession(User $user)
    {
        $session = $user->trackPoints->first();

        return $this->pointsSession->end($session);
    }
}
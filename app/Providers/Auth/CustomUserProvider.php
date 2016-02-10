<?php

namespace App\Providers\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Http\Request;
use App\Channel;

class CustomUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * @var Channel $channel
     */
    private $channel;


    public function __construct(HasherContract $hasher, Request $request, $model)
    {
        parent::__construct($hasher, $model);

        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->whereHas('channels', function($query) {
            $query->where('slug', $this->channel->slug);
        })->find($identifier);
    }
}

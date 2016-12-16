<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;
use App\Events\NewFollower;
use App\Followers\Manager;

class FollowersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.auth', 'auth.api:followers']);
    }

    public function store(Request $request, Channel $channel, Manager $followersManager)
    {
        $followers = $followersManager->add($channel, (array) $request->get('followers', []));

        return response()->json([
            'ok'  => 'success',
            'new' => $followers['new'],
            're'  => $followers['re']
        ]);
    }
}

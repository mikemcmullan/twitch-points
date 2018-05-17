<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Channel;
use Redis;

class QueueController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
		$this->middleware(['auth', 'featureDetection:queue']);
    }

    /**
     * Show the application dashboard to the user.
     */
    public function index(Channel $channel)
    {
		$status = (bool) Redis::get("{$channel->id}:queueOpen") ? 'open' : 'closed';

        return view('queue', ['status' => $status]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Channel;

class ChatLogsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard to the user.
     */
    public function index(Channel $channel)
    {
        return view('chat-logs');
    }
}

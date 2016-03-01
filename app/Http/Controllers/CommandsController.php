<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Channel;

class CommandsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index(Channel $channel)
    {
        if (\Auth::user()) {
            return view('commands');
        }

        return view('commands-public');
    }
}

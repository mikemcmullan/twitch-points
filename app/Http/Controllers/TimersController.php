<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Channel;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TimersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'featureDetection:timers']);
    }

    public function index(Channel $channel)
    {
        if (\Gate::denies('access-page', [$channel, 'timers'])) {
            return $this->redirectHomeWithMessage();
        }

        return view('timers');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TimersController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        if (\Gate::denies('access-page', 'timers')) {
            return $this->redirectHomeWithMessage();
        }

        return view('timers');
    }
}

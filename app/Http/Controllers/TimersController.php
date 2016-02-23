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
        return view('timers');
    }
}

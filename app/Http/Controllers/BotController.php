<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BotController extends Controller {

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['botControl']]);
    }

    public function botControl()
    {
        return view('bot-control');
    }
}

<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuotesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['featureDetection:quotes']);
    }

    public function index(Channel $channel)
    {
        if (\Gate::allows('admin-channel', $channel)) {
            return view('quotes');
        }

        return view('quotes-public');
    }
}

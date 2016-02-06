<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Channel;
use App\Jobs\GetViewerJob;
use App\Http\Controllers\Controller;
use App\Exceptions\UnknownHandleException;
use Exception;
use InvalidArgumentException;

class ViewerController extends Controller
{
    use DispatchesJobs;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getViewer(Request $request, Channel $channel)
    {
        $handle = $request->get('handle');

        $response = $this->dispatch(new GetViewerJob($channel, $handle));
        $response['time_online'] = presentTimeOnline($response['minutes']);

        return response()->json($response);
    }
}

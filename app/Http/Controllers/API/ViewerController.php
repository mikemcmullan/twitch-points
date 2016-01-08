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

        try {
            $response = $this->dispatch(new GetViewerJob($channel, $handle));
            $code = 200;

            $response['time_online'] = presentTimeOnline($response['minutes']);
        } catch (UnknownHandleException $e) {
            $response = [
                'error' => 'Not Found',
                'code'  => $code = 404,
                'message' => $e->getMessage()
            ];
        } catch (InvalidArgumentException $e) {
            $response = [
                'error' => 'Bad Request',
                'code'  => $code = 400,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $response = [
                'error' => 'Internal Server Error',
                'code'  => $code = 500,
                'message' => 'Unknown error occurred.'
            ];
        }

        return response()->json($response, $code);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Channel;
use App\Exceptions\GiveAwayException;
use App\Exceptions\UnknownHandleException;
use App\GiveAways\Manager;
use App\Jobs\GiveAways\EnterGiveAwayJob;
use App\Jobs\GiveAways\StartGiveAwayJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use InvalidArgumentException;

class GiveAwayController extends Controller
{
    use DispatchesJobs;

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('protect.api');
    }


    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enter(Request $request, Channel $channel)
    {
        $handle = $request->get('handle');
        $tickets = $request->get('tickets');

        try {
            $response = $this->dispatch(new EnterGiveAwayJob($channel, $handle, $tickets));
            $code = 200;
            $response = [
                'status' => $response
            ];
        } catch (InvalidArgumentException $e) {
            $response = [
                'error' => 'Bad Request',
                'code'  => $code = 400,
                'message' => $e->getMessage()
            ];
        } catch(UnknownHandleException $e) {
            $response = [
                'error' => 'Not Found',
                'code'  => $code = 404,
                'message' => $e->getMessage()
            ];
        } catch (GiveAwayException $e) {
            $response = [
                'error' => 'Conflict',
                'code'  => $code = 409,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, $code);
    }

}

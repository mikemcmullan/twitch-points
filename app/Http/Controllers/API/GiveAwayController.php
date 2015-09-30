<?php

namespace App\Http\Controllers\API;

use App\Channel;
use App\Exceptions\GiveAwayException;
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

            $response = [
                'status' => $response
            ];
        } catch (InvalidArgumentException $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        } catch (GiveAwayException $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }

}

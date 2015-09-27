<?php

namespace App\Http\Controllers\API;

use App\Channel;
use App\Jobs\GiveAways\EnterGiveAwayCommand;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Commands\GetViewerCommand;
use App\Exceptions\UnknownHandleException;
use App\Http\Controllers\Controller;
use Exception;
use InvalidArgumentException;
use Bus;

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

//        try {
            $response = $this->dispatch(new EnterGiveAwayCommand($channel, $handle));

//            $response['time_online'] = presentTimeOnline($response['minutes']);
//        } catch (UnknownHandleException $e) {
//            $response = [
//                'level' => 'notice',
//                'error' => $e->getMessage()
//            ];
//        } catch (InvalidArgumentException $e) {
//            $response = [
//                'level' => 'notice',
//                'error' => $e->getMessage()
//            ];
//        } catch (Exception $e) {
//            $response = [
//                'level' => 'notice',
//                'error' => 'Unknown error occurred.'
//            ];
//        }

//        return response()->json($response);
    }
}

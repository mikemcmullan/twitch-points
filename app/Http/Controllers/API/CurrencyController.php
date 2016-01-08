<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Channel;
use App\Http\Requests;
use App\Jobs\AddCurrencyJob;
use App\Jobs\RemoveCurrencyJob;
use Exception;
use InvalidArgumentException;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\UnknownHandleException;

class CurrencyController extends Controller
{
    use DispatchesJobs;

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('protect.api', ['only' => ['addPoints', 'removePoints']]);
    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addPoints(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'target', 'points']);

        try {
            $response = $this->dispatch(new AddCurrencyJob($channel, $data['handle'], $data['target'], $data['points']));
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

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removePoints(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'target', 'points']);

        try {
            $response = $this->dispatch(new RemoveCurrencyJob($channel, $data['handle'], $data['target'], $data['points']));
        } catch (UnknownHandleException $e) {
            $response = [
                'error' => $e->getMessage(),
                'level' => 'regular'
            ];
        } catch (AccessDeniedException $e) {
            $response = [
                'error' => $e->getMessage(),
                'level' => 'regular'
            ];
        } catch (InvalidArgumentException $e) {
            $response = [
                'error' => $e->getMessage(),
                'level' => 'regular'
            ];
        } catch (Exception $e) {
            $response = [
                'error' => 'Unknown error occurred.',
                'level' => 'regular'
            ];
        }

        return response()->json($response);
    }
}

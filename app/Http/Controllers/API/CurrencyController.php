<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Channel;
use App\Http\Requests;
use App\Jobs\AddCurrencyJob;
use App\Jobs\RemoveCurrencyJob;
use App\Jobs\StopCurrencySystemJob;
use App\Jobs\StartCurrencySystemJob;
use Exception;
use InvalidArgumentException;
use App\Exceptions\UnknownHandleException;

class CurrencyController extends Controller
{
    use DispatchesJobs;

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCurrency(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'points']);

        try {
            $response = $this->dispatch(new AddCurrencyJob($channel, $data['handle'], $data['points']));
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
    public function removeCurrency(Request $request, Channel $channel)
    {
        $data = $request->only(['handle', 'points']);

        try {
            $response = $this->dispatch(new RemoveCurrencyJob($channel, $data['handle'], $data['points']));
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

    public function startSystem(Request $request, Channel $channel)
    {
        $this->dispatch(new StartCurrencySystemJob($channel));

        return response()->json(['ok' => 'success']);
    }

    public function stopSystem(Request $request, Channel $channel)
    {
        $this->dispatch(new StopCurrencySystemJob($channel));

        return response()->json(['ok' => 'success']);
    }
}

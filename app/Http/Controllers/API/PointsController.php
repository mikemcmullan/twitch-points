<?php

namespace App\Http\Controllers\API;

use App\Channel;
use App\Commands\AddPointsCommand;
use App\Commands\RemovePointsCommand;
use App\Contracts\Repositories\ChannelRepository;
use App\Contracts\Repositories\ChatterRepository;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\UnknownHandleException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Bus;
use Exception;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PointsController extends Controller
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * @var ChannelRepository
     */
    private $channelRepository;

    /**
     * Default channel.
     *
     * @var
     */
    private $channel;

    /**
     * @param ChatterRepository $chatterRepository
     * @param ChannelRepository $channelRepository
     */
    public function __construct(ChatterRepository $chatterRepository, ChannelRepository $channelRepository)
    {
        $this->chatterRepository = $chatterRepository;
        $this->channelRepository = $channelRepository;

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
            $response = Bus::dispatch(new AddPointsCommand($channel, $data['handle'], $data['target'], $data['points']));
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
            $response = Bus::dispatch(new RemovePointsCommand($channel, $data['handle'], $data['target'], $data['points']));
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

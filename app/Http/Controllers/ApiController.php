<?php namespace App\Http\Controllers;

use App\Commands\AddPointsCommand;
use App\Commands\RemovePointsCommand;
use App\Commands\GetViewerCommand;
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

class ApiController extends Controller {

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

		$this->channel = \Config::get('twitch.points.default_channel');

		$this->middleware('protect.api', ['only' => ['addPoints', 'removePoints']]);
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getViewer(Request $request)
	{
		$handle = $request->get('handle');

		try
		{
			$response = Bus::dispatch(new GetViewerCommand($this->channel, $handle));
		}
		catch(UnknownHandleException $e)
		{
			$response = ['error' => $e->getMessage()];
		}
		catch(InvalidArgumentException $e)
		{
			$response = ['error' => $e->getMessage()];
		}
		catch(Exception $e)
		{
			$response = ['error' => 'Unknown error occurred.'];
		}

		return response()->json($response);
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function addPoints(Request $request)
	{
		$data = $request->only(['handle', 'target', 'points']);

		try
		{
			$response = Bus::dispatch(new AddPointsCommand($this->channel, $data['handle'], $data['target'], $data['points']));
		}
		catch(UnknownHandleException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(AccessDeniedException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(InvalidArgumentException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(Exception $e)
		{}

		return response()->json($response);
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function removePoints(Request $request)
	{
		$data = $request->only(['handle', 'target', 'points']);

		try
		{
			$response = Bus::dispatch(new RemovePointsCommand($this->channel, $data['handle'], $data['target'], $data['points']));
		}
		catch(UnknownHandleException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(AccessDeniedException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(InvalidArgumentException $e)
		{
			$response = [
				'error' => $e->getMessage(),
				'level' => 'regular'
			];
		}
		catch(Exception $e)
		{}

		return response()->json($response);
	}
}

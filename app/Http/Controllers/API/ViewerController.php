<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Commands\GetViewerCommand;
use App\Exceptions\UnknownHandleException;
use App\Http\Controllers\Controller;
use Exception;
use InvalidArgumentException;
use Bus;

class ViewerController extends Controller {

	/**
	 *
	 */
	public function __construct()
	{
		$this->channel = \Config::get('twitch.points.default_channel');
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

			$response['time_online'] = presentTimeOnline($response['minutes']);
		}
		catch(UnknownHandleException $e)
		{
			$response = [
				'level' => 'notice',
				'error' => $e->getMessage()
			];
		}
		catch(InvalidArgumentException $e)
		{
			$response = [
				'level' => 'notice',
				'error' => $e->getMessage()
			];
		}
		catch(Exception $e)
		{
			$response = [
				'level' => 'notice',
				'error' => 'Unknown error occurred.'
			];
		}

		return response()->json($response);
	}

}
<?php

namespace App\Http\Controllers\API;

use App\Contracts\Repositories\ChannelRepository;
use App\Http\Controllers\Controller;
use App\Bot\Manager;
use Illuminate\Http\Request;

class BotController extends Controller {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var BotManager
	 */
	private $bot;

	public function __construct(Request $request, Manager $bot)
	{
		$this->request = $request;
		$this->bot = $bot;
		$this->channel = \Config::get('twitch.points.default_channel');
	}

	public function getLog()
	{
		$offset = (int) $this->request->get('offset', 0);
		$log = $this->bot->getLog($offset);

		$response = [
			'status'    => $this->bot->getStatus(),
			'new_offset'=> count($log) + $offset,
			'entries'   => $log
		];

		return response()->json($response);
	}

	public function joinChannel(ChannelRepository $channelRepo)
	{
		$channel = $channelRepo->findByName($this->channel);

		if ($channel)
		{
			$response = $this->bot->joinChannel($channel);
		}
		else
		{
			$response = [
				'error' => 'Invalid channel.',
				'level' => 'regular'
			];
		}

		return response()->json($response);
	}

	public function leaveChannel(ChannelRepository $channelRepo)
	{
		$channel = $channelRepo->findByName($this->channel);

		if ($channel)
		{
			$response = $this->bot->leaveChannel($channel);
		}
		else
		{
			$response = [
				'error' => 'Invalid channel.',
				'level' => 'regular'
			];
		}

		return response()->json($response);
	}

	public function startBot()
	{
		return response()->json($this->bot->startBot());
	}

	public function stopBot()
	{
		return response()->json($this->bot->stopBot());
	}

}
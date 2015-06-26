<?php namespace App\Http\Controllers;

use App\Contracts\Repositories\ChannelRepository;
use App\Contracts\Repositories\ChatterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

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
	 * @param ChatterRepository $chatterRepository
	 * @param ChannelRepository $channelRepository
	 */
	public function __construct(ChatterRepository $chatterRepository, ChannelRepository $channelRepository)
	{
		$this->chatterRepository = $chatterRepository;
		$this->channelRepository = $channelRepository;
	}

	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function points(Request $request)
	{
		$handle = strtolower($request->get('handle'));
		$channel = $this->channelRepository->findByName(\Config::get('twitch.points.default_channel'));

		$chatter = $this->chatterRepository->findByHandle($channel, $handle);

		if ( ! $handle)
		{
			return response('No handle provided.');
		}

		if ( ! $chatter)
		{
			return response(sprintf('%s does not have any points yet. Please try again later.', $handle));
		}

		$time = $chatter['minutes'] / 60;
		$timeUnit = 'hours';

		if ($time < 1)
		{
			$time = $chatter['minutes'];
			$timeUnit = 'minutes';
		}

		return response(sprintf('%s has %d points and has been here for %d %s', $chatter['handle'], $chatter['points'], $time, $timeUnit));
	}

}

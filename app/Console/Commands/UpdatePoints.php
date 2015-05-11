<?php namespace App\Console\Commands;

use App\Commands\DownloadChatList;
use App\Contracts\Repositories\ChannelRepository;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdatePoints extends Command {

	use DispatchesCommands;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'points:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update chat list for a channel.';

	/**
	 * @var TrackPointsSession
	 */
	private $pointsSession;

	/**
	 * @var ChannelRepository
	 */
	private $channelRepository;

	/**
	 * Create a new command instance.
	 *
	 * @param TrackSessionRepository $pointsSession
	 * @param ChannelRepository $channelRepository
	 */
	public function __construct(TrackSessionRepository $pointsSession, ChannelRepository $channelRepository)
	{
		parent::__construct();
		$this->pointsSession = $pointsSession;
		$this->channelRepository = $channelRepository;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$startTime = microtime(true);
		$sessions = $this->pointsSession->allUncompletedSessions();

		foreach ($sessions as $session)
		{
			$this->dispatch(new DownloadChatList($session->channel));
		}

		$end = microtime(true) - $startTime;
		\Log::info(sprintf('Execution time: %s', $end));
		$this->info(sprintf('Command executed in %s seconds', $end));
	}
}

<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Contracts\Repositories\TrackSessionRepository;
use Illuminate\Foundation\Bus\DispatchesCommands;
use App\Commands\RankChattersCommand;

class RankChatters extends Command {

	use DispatchesCommands;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'points:rank';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rank Chatters.';

	/**
	 * @var TrackSessionRepository
	 */
	protected $pointsSession;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(TrackSessionRepository $pointsSession)
	{
		parent::__construct();
		$this->pointsSession = $pointsSession;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$startTime = microtime(true);
		$sessions = $this->pointsSession->allIncompletedSessions();

		foreach ($sessions as $session) {
			$this->dispatch(new RankChattersCommand($session->channel));
		}

		$end = microtime(true) - $startTime;
		\Log::info(sprintf('Ranked Chatters in %s seconds', $end));
		$this->info(sprintf('Ranked Chatters in %s seconds', $end));
	}
}

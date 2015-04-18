<?php namespace App\Console\Commands;

use App\Commands\UpdatePointsCommand;
use App\Exceptions\InvalidChannelException;
use App\Exceptions\StreamOfflineException;
use App\Contracts\Repositories\ChatterRepository;
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
	 * @var ChatterRepository
	 */
	private $chatterRepository;

	/**
	 * @var TrackPointsSession
	 */
	private $pointsSession;

	/**
	 * Create a new command instance.
	 *
	 * @param ChatterRepository $chatterRepository
	 * @param TrackSessionRepository $pointsSession
	 */
	public function __construct(ChatterRepository $chatterRepository, TrackSessionRepository $pointsSession)
	{
		parent::__construct();
		$this->chatterRepository = $chatterRepository;
		$this->pointsSession = $pointsSession;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$sessions = $this->pointsSession->allUncompletedSessions();
		$startTime = microtime(true);

		foreach ($sessions as $session)
		{
			$this->runUpdate($session['user']);
		}

		$end = microtime(true) - $startTime;
		$this->info(memory_get_usage());
		\Log::info(sprintf('Execution time: %s', $end));
		$this->info(sprintf('Command executed in %s seconds', $end));
	}

	/**
	 * @param $channel
	 */
	private function runUpdate($channel)
	{
		try
		{
			$response = $this->dispatch(new UpdatePointsCommand($channel, $this->chatterRepository));
		}
		catch (InvalidChannelException $e)
		{
			$this->error(sprintf('Channel "%s" is not valid.', $e->getMessage()));
		}
		catch (StreamOfflineException $e)
		{
			$this->error(sprintf('Channel "%s" is offline.', $e->getMessage()));
		}
		catch (\Exception $e)
		{
			$trace = $e->getTrace();
			$class = $trace[0]['class'];

			$this->error(sprintf("[%s]\n\n%s", $class, $e->getMessage()));
		}
	}
}

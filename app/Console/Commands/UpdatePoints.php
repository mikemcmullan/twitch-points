<?php namespace App\Console\Commands;

use App\Commands\UpdatePointsCommand;
use App\Exceptions\InvalidChannelException;
use App\Exceptions\StreamOfflineException;
use App\Repositories\ChatUsers\ChatUserRepository;
use App\Repositories\TrackPointsSessions\TrackSessionRepository;
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
	 * @var ChatUserRepository
	 */
	private $chatUserRepository;

	/**
	 * @var TrackPointsSession
	 */
	private $pointsSession;

	/**
	 * Create a new command instance.
	 *
	 * @param ChatUserRepository $chatUserRepository
	 * @param TrackSessionRepository $pointsSession
	 */
	public function __construct(ChatUserRepository $chatUserRepository, TrackSessionRepository $pointsSession)
	{
		parent::__construct();
		$this->chatUserRepository = $chatUserRepository;
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
			$response = $this->dispatch(new UpdatePointsCommand($channel, $this->chatUserRepository));

			if ($response instanceof StreamOfflineException || $response instanceof InvalidChannelException)
			{
				throw new $response($response->getMessage());
			}
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

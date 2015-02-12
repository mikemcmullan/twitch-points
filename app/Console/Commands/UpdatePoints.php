<?php namespace App\Console\Commands;

use App\Commands\UpdatePoints as UpdatePointsCommand;
use App\Exceptions\InvalidChannelException;
use App\Exceptions\StreamOfflineException;
use App\Repositories\ChatUsers\ChatUserRepository;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesCommands;
use PHPBenchTime\Timer;
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
	 * Create a new command instance.
	 * @param ChatUserRepository $chatUserRepository
	 */
	public function __construct(ChatUserRepository $chatUserRepository)
	{
		parent::__construct();
		$this->chatUserRepository = $chatUserRepository;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$channel = $this->argument('channel');

		$timer = new Timer();
		$timer->start();

		try
		{
			$response = $this->dispatch(new UpdatePointsCommand($channel, $this->chatUserRepository));

			if ($response instanceof StreamOfflineException)
			{
				throw new $response($response->getMessage());
			}
		}
		catch(InvalidChannelException $e)
		{
			$this->error(sprintf('Channel "%s" is not valid.', $e->getMessage()));
		}
		catch(StreamOfflineException $e)
		{
			$this->error(sprintf('Channel "%s" is offline.', $e->getMessage()));
		}
		catch(\Exception $e)
		{
			$trace = $e->getTrace();
			$class = $trace[0]['class'];

			$this->error(sprintf("[%s]\n\n%s", $class, $e->getMessage()));
		}

		$end = $timer->end()['total'];
		\Log::info(sprintf('Execution time: %s', $end));
		$this->info(sprintf('Command executed in %s seconds', $end));
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['channel', InputArgument::REQUIRED, 'Channel Name.'],
		];
	}
}

<?php

namespace App\Bot;

use App\Channel;
use App\Exceptions\BotStateException;
use Illuminate\Redis\Database;
use Supervisor\Supervisor;

class Manager {

	/**
	 * @var Supervisor
	 */
	private $supervisor;

	/**
	 * @var
	 */
	private $redis;

	/**
	 * @param Supervisor $supervisor
	 * @param Database $redis
	 */
	public function __construct(Supervisor $supervisor, Database $redis)
	{
		$this->supervisor = $supervisor;
		$this->redis = $redis;
	}

	/**
	 * @return \Supervisor\Process
	 */
	public function getProcess()
	{
		return $this->supervisor->getProcess('twitch_bot');
	}

	/**
	 * @param int $offset
	 *
	 * @return array
	 */
	public function getLog($offset = 0)
	{
		return $this->redis->lrange('twitch-bot-log', 0, -1);
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->getProcess()['statename'];
	}

	/**
	 * @return bool
	 * @throws BotStateException
	 */
	public function startBot()
	{
		$this->guardProcessRunning();

		$name = $this->getProcess()->getName();

		return $this->supervisor->startProcess($name);
	}

	/**
	 * @return bool
	 * @throws BotStateException
	 */
	public function stopBot()
	{
		$this->guardProcessNotRunning();

		$name = $this->getProcess()->getName();

		return $this->supervisor->stopProcess($name);
	}

	/**
	 * @param Channel $channel
	 *
	 * @return mixed
	 * @throws BotStateException
	 */
	public function joinChannel(Channel $channel)
	{
		$this->guardProcessNotRunning();

		return $this->redis->publish("irc:{$channel['id']}:commander", 'join-chat');
	}

	/**
	 * @param Channel $channel
	 *
	 * @return mixed
	 * @throws BotStateException
	 */
	public function leaveChannel(Channel $channel)
	{
		$this->guardProcessNotRunning();

		return $this->redis->publish("irc:{$channel['id']}:commander", 'leave-chat');
	}

	/**
	 * @throws BotStateException
	 */
	private function guardProcessNotRunning()
	{
		if ( ! $this->getProcess()->isRunning())
		{
			throw new BotStateException('Bot is not running.');
		}
	}

	/**
	 * @throws BotStateException
	 */
	private function guardProcessRunning()
	{
		if ($this->getProcess()->isRunning())
		{
			throw new BotStateException('Bot is already running.');
		}
	}
}
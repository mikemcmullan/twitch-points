<?php

namespace App\ManagePoints;

use App\Exceptions\UnknownUserException;
use App\Exceptions\UnknownHandleException;
use App\Channel;

trait ManagePointsTrait {

	/**
	 * Calculate the new points total.
	 *
	 * @param $currentPoints
	 * @param $points
	 * @param $sign
	 *
	 * @return mixed
	 */
	private function calculateTotalPoints($currentPoints, $points, $sign)
	{
		if ($sign === '-')
		{
			return (int) $currentPoints - $points;
		}

		return (int) $currentPoints + $points;
	}

	/**
	 * Get a chatter by their handle who belongs to a channel.
	 *
	 * @param Channel $channel
	 * @param $handle
	 *
	 * @return mixed
	 * @throws UnknownHandleException
	 * @throws UnknownUserException
	 */
	private function getChatter(Channel $channel, $handle)
	{
		$chatter = $this->getChatterRepository()->findByHandle($channel, $handle);

		if ( ! $chatter)
		{
			throw new UnknownHandleException;
		}

		return $chatter;
	}

	private function resolveChannel($channel)
	{
		if ( ! $channel instanceof Channel)
		{
			$channel = $this->getChannelRepository()->findByName($channel);
		}

		if ( ! $channel)
		{
			throw new UnknownUserException;
		}

		return $channel;
	}

	/**
	 * @param $channel      The channel the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 * @param string $sign Indicate whether you are adding or removing points.
	 *                      Must be either + or -.
	 *
	 * @return mixed
	 * @throws UnknownHandleException
	 * @throws UnknownUserException
	 */
	private function updatePoints($channel, $handle, $points, $sign = '+')
	{
		if ( ! is_numeric($points))
		{
			throw new \InvalidArgumentException('Points must be an numeric.');
		}

		if ( ! in_array($sign, ['-', '+']))
		{
			throw new \InvalidArgumentException('Sign must be either + or -.');
		}

		$channel = $this->resolveChannel($channel);

		$chatter = $this->getChatter($channel, $handle);

		$this->getChatterRepository()->update($channel, $chatter['handle'], 0, $sign . $points);

		return $this->calculateTotalPoints($chatter['points'], $points, $sign);
	}

	/**
	 * Get points for a chatter.
	 *
	 * @param $channel
	 * @param $handle
	 *
	 * @return mixed
	 */
	public function getPoints($channel, $handle)
	{
		$channel = $this->resolveChannel($channel);
		$chatter = $this->getChatter($channel, $handle);

		return floor($chatter['points']);
	}

	/**
	 * @param $channel      The channel the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 *
	 * @return mixed
	 */
	public function addPoints($channel, $handle, $points)
	{
		return $this->updatePoints($channel, $handle, $points);
	}

	/**
	 * @param $channel      The channel the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 *
	 * @return mixed
	 */
	public function removePoints($channel, $handle, $points)
	{
		return $this->updatePoints($channel, $handle, $points, '-');
	}

}
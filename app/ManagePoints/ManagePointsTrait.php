<?php

namespace App\ManagePoints;

use App\Exceptions\AccessDeniedException;
use App\Exceptions\UnknownUserException;
use App\Exceptions\UnknownHandleException;
use App\Channel;
use InvalidArgumentException;

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
			$total = (int) $currentPoints - $points;
		}
		else
		{
			$total = (int) $currentPoints + $points;
		}

		if ($total < 0)
		{
			return 0;
		}

		return $total;
	}

	/**
	 * Get a chatter by their handle who belongs to a channel.
	 *
	 * @param Channel $channel
	 * @param $handle
	 *
	 * @return mixed
	 *
	 * @throws UnknownHandleException
	 * @throws UnknownUserException
	 */
	private function getChatter(Channel $channel, $handle)
	{
		if ( ! $handle)
		{
			throw new UnknownHandleException(sprintf('%s is not a valid handle.', $handle));
		}

		$chatter = $this->getChatterRepository()->findByHandle($channel, $handle);

		if ( ! $chatter)
		{
			throw new UnknownHandleException(sprintf('%s is not a valid handle.', $handle));
		}

		return $chatter;
	}

	/**
	 * Get instance of channel object if channel name is provided.
	 *
	 * @param $channel
	 *
	 * @return mixed
	 *
	 * @throws UnknownUserException
	 */
	private function resolveChannel($channel)
	{
		if ( ! $channel instanceof Channel)
		{
			$channel = $this->getChannelRepository()->findByName($channel);
		}

		if ( ! $channel)
		{
			throw new UnknownUserException(sprintf('%s is not a valid channel.', $channel));
		}

		return $channel;
	}

	private function validate($command)
	{
		if ($command->handle === null || $command->points === null || $command->target === null)
		{
			throw new InvalidArgumentException('handle, points and target are required parameters.');
		}

		if (is_numeric($command->points) === false || $command->points < 0)
		{
			throw new InvalidArgumentException('points value must be greater than zero.');
		}

		if ($command->points > 1000)
		{
			throw new InvalidArgumentException('You cannot award or take away more than 1000 points at a time.');
		}
	}

	/**
	 * Validate if the symbol value fits the criteria.
	 *
	 * @param $symbol
	 *
	 * @throws InvalidArgumentException
	 */
	private function validateSymbol($symbol)
	{
		if ( ! in_array($symbol, ['-', '+']))
		{
			throw new InvalidArgumentException('Symbol must be either + or -.');
		}
	}

	/**
	 * Validate if a chatter is a mod.
	 *
	 * @param $chatter
	 *
	 * @throws AccessDeniedException
	 */
	private function validateIfMod($chatter)
	{
		if ( ! (bool) array_get($chatter, 'mod'))
		{
			throw new AccessDeniedException(sprintf('%s is not a mod.', $chatter['handle']));
		}
	}

	/**
	 * @param $channel          The channel the handle belongs to.
	 * @param $handle           The chat handle of the user.
	 * @param $target           The chat handle of the user receiving or losing points.
	 * @param $points           How many points are being added or removing.
	 * @param string $symbol    Indicate whether you are adding or removing points.
	 *                          Must be either + or -.
	 *
	 * @return mixed
	 * @throws AccessDeniedException
	 * @throws UnknownHandleException
	 * @throws UnknownUserException
	 */
	private function updatePoints($channel, $handle, $target, $points, $symbol = '+')
	{
		$this->validateSymbol($symbol);

		$channel = $this->resolveChannel($channel);
		$chatter = $this->getChatter($channel, $handle);

		$this->validateIfMod($chatter);

		$target     = $this->getChatter($channel, $target);
		$pointTotal = $this->calculateTotalPoints($target['points'], $points, $symbol);

		// Make sure chatter does not get negative points.
		if ($pointTotal === 0)
		{
			$points = $target['points'];
		}

		$this->getChatterRepository()->updateChatter($channel, $target['handle'], 0, $symbol . $points);

		return [
			'channel'=> $channel['name'],
			'handle' => $target['handle'],
			'points' => floor($pointTotal),
			'minutes'=> (int) $target['minutes']
		];
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

		return $this->getChatter($channel, $handle);
	}

	/**
	 * @param $channel      The channel the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $target       The chat handle of the user receiving the points.
	 * @param $points       How many points are being added or removing.
	 *
	 * @return mixed
	 */
	public function addPoints($channel, $handle, $target, $points)
	{
		return $this->updatePoints($channel, $handle, $target, $points);
	}

	/**
	 * @param $channel      The channel the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $target       The chat handle of the user losing the points.
	 * @param $points       How many points are being added or removing.
	 *
	 * @return mixed
	 */
	public function removePoints($channel, $handle, $target, $points)
	{
		return $this->updatePoints($channel, $handle, $target, $points, '-');
	}
}
<?php

namespace App\ManagePoints;

use App\UnknownHandleException;
use App\User;

trait UpdatePointsTrait {

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
	 * @param User $user The user the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 * @param string $sign Indicate whether you are adding or removing points.
	 *                      Must be either + or -.
	 *
	 * @return mixed
	 * @throws UnknownHandleException
	 */
	private function updatePoints(User $user, $handle, $points, $sign = '+')
	{
		if ( ! is_numeric($points))
		{
			throw new \InvalidArgumentException('Points must be an numeric.');
		}

		if ( ! in_array($sign, ['-', '+']))
		{
			throw new \InvalidArgumentException('Sign must be either + or -.');
		}

		$chatter = $this->chatterRepository->findByHandle($user, $handle);

		if ($chatter)
		{
			$this->chatterRepository->update($user, $chatter['handle'], 0, $sign . $points);

			return $this->calculateTotalPoints($chatter['points'], $points, $sign);
		}

		throw new UnknownHandleException;
	}

	/**
	 * @param User $user    The user the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 */
	public function addPoints(User $user, $handle, $points)
	{
		return $this->updatePoints($user, $handle, $points);
	}

	/**
	 * @param User $user    The user the handle belongs to.
	 * @param $handle       The chat handle of the user.
	 * @param $points       How many points are being added or removing.
	 */
	public function removePoints(User $user, $handle, $points)
	{
		return $this->updatePoints($user, $handle, $points, '-');
	}

}
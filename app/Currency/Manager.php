<?php

namespace App\Currency;

use App\Contracts\Repositories\ChatterRepository;
use App\Exceptions\UnknownUserException;
use App\Exceptions\UnknownHandleException;
use App\Channel;
use InvalidArgumentException;
use App\Support\ScoreboardCache;

class Manager
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepo;

    /**
     * @var ScoreboardCache
     */
    private $scoreboardCache;

    /**
     * @param ChatterRepository $chatterRepo
     * @param ChannelRepository $channelRepo
     */
    public function __construct(ChatterRepository $chatterRepo, ScoreboardCache $scoreboardCache)
    {
        $this->chatterRepo = $chatterRepo;
        $this->scoreboardCache = $scoreboardCache;
    }

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
        if ($sign === '-') {
            $total = (int) $currentPoints - $points;
        } else {
            $total = (int) $currentPoints + $points;
        }

        if ($total < 0) {
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
    public function getViewer(Channel $channel, $handle)
    {
        if (! $handle) {
            throw new UnknownHandleException(sprintf('%s is not a valid handle.', $handle));
        }

        $handle = strtolower($handle);

        // $chatter = $this->chatterRepo->findByHandle($channel, $handle);
        $chatter = $this->scoreboardCache->findByHandle($channel, $handle);

        if (! $chatter) {
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
        if (! $channel instanceof Channel) {
            $channel = \App\Channel::findByName($channel);
        }

        if (! $channel) {
            throw new UnknownUserException(sprintf('%s is not a valid channel.', $channel));
        }

        return $channel;
    }

    /**
     * Validate if the command parameters are valid.
     *
     * @param $command
     * @param $handle
     * @param $points
     */
    private function validate($channel, $handle, $points, $source = null)
    {
        if ($handle === null || $points === null) {
            throw new InvalidArgumentException('handle and points are required parameters.');
        }

        if (is_numeric($points) === false || $points < 0) {
            throw new InvalidArgumentException('points value must be greater than zero.');
        }

        if ($points > 10000) {
            throw new InvalidArgumentException('You cannot award or take away more than 10000 points at a time.');
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
        if (! in_array($symbol, ['-', '+'])) {
            throw new InvalidArgumentException('Symbol must be either + or -.');
        }
    }

    /**
     * @param string $channel The channel the handle belongs to.
     * @param string $handle  The chat handle of the user.
     * @param string $target  The chat handle of the user receiving or losing points.
     * @param int $points     How many points are being added or removing.
     * @param string $source  The chat handle of the user that points will be taken from
     *                        when awarding about chat handle.
     * @param string $symbol  Indicate whether you are adding or removing points.
     *                        Must be either + or -.
     *
     * @return mixed
     * @throws UnknownHandleException
     * @throws UnknownUserException
     */
    private function updatePoints($channel, $handle, $points, $source = null, $symbol = '+')
    {
        $this->validateSymbol($symbol);

        $channel = $this->resolveChannel($channel);
        $sourceChatter = null;
        $points = (int) $points;

        if ($handle === $source) {
            throw new InvalidArgumentException(sprintf('You cannot give yourself %s.', strtolower($channel->getSetting('currency.name'))));
        }

        if ($source && $sourceChatter = $this->getViewer($channel, $source)) {
            if ($sourceChatter['points'] < $points) {
                throw new \InvalidArgumentException(sprintf('%s does not have %s %s to give to %s.', $sourceChatter['handle'], $points, strtolower($channel->getSetting('currency.name')), $handle));
            }

            $this->chatterRepo->updateChatter($channel, $sourceChatter['handle'], 0, '-' . $points);
            $this->scoreboardCache->addViewer($channel, array_merge($sourceChatter, [
                'points' => $this->calculateTotalPoints($sourceChatter['points'], $points, '-')
            ]));
        }

        $chatter = $this->getViewer($channel, $handle);
        $pointTotal = $this->calculateTotalPoints($chatter['points'], $points, $symbol);

        // Make sure chatter does not get negative points.
        if ($pointTotal === 0) {
            $points = $chatter['points'];
        }

        $this->chatterRepo->updateChatter($channel, $chatter['handle'], 0, $symbol . $points);
        $this->scoreboardCache->addViewer($channel, array_merge($chatter, ['points' => $pointTotal]));

        return array_merge(array_only($chatter, ['handle', 'minutes']), [
            'channel' => $channel->name,
            'points' => floor($pointTotal),
            'amount' => floor($points),
            'source' => $source,
            'source_display_name' => getDisplayName($source),
            'username' => $chatter['handle'],
            'display_name' => getDisplayName($chatter['handle'])
        ]);
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

        return $this->getViewer($channel, $handle)['points'];
    }

    /**
     * @param string $channel       The channel the handle belongs to.
     * @param string $handle        The chat handle of the user.
     * @param string $target        The chat handle of the user receiving the points.
     * @param int $points           How many points are being added or removing.
     * @param string|null $source   If provided this is who the points will be taken from.
     * @return mixed
     */
    public function addPoints($channel, $handle, $points, $source = null)
    {
        $this->validate($channel, $handle, $points, $source);

        return $this->updatePoints($channel, $handle, $points, $source);
    }

    /**
     * @param string $channel      The channel the handle belongs to.
     * @param string $handle       The chat handle of the user.
     * @param string $target       The chat handle of the user losing the points.
     * @param string $points       How many points are being added or removing.
     *
     * @return mixed
     */
    public function removePoints($channel, $handle, $points)
    {
        $this->validate($channel, $handle, $points);

        return $this->updatePoints($channel, $handle, $points, null, '-');
    }

    /**
     * Alias for addPoints.
     *
     * @param $channel
     * @param $handle
     * @param $target
     * @param $points
     * @param $source
     */
    public function add($channel, $handle, $points, $source = null)
    {
        return $this->addPoints($channel, $handle, $points, $source);
    }

    /**
     * Alias for RemovePoints.
     *
     * @param $channel
     * @param $handle
     * @param $target
     * @param $points
     */
    public function remove($channel, $handle, $points)
    {
        return $this->removePoints($channel, $handle, $points);
    }
}

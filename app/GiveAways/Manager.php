<?php

namespace App\GiveAways;

use App\Channel;
use App\Contracts\Repositories\ChatterRepository;
use App\Currency\Manager as CurrencyManager;
use App\Events\GiveAwayWasEntered;
use App\Events\GiveAwayWasReset;
use App\Events\GiveAwayWasStarted;
use App\Events\GiveAwayWasStopped;
use App\Exceptions\GiveAwayException;
use App\Exceptions\UnknownHandleException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Redis\Database;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class Manager
{
    /**
     * @var CurrencyManager
     */
    private $currencyManager;

    /**
     * @var int
     */
    private $ticketCost;

    /**
     * @var int
     */
    private $maxTickets;

    /**
     * @var Database
     */
    private $redis;

    /**
     * @var ChatterRepository
     */
    private $chatterRepo;

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * @param CurrencyManager $currencyManager
     */
    public function __construct(CurrencyManager $currencyManager, ChatterRepository $chatterRepo, Database $redis, Dispatcher $events)
    {
        $this->currencyManager = $currencyManager;
        $this->redis = $redis;
        $this->chatterRepo = $chatterRepo;
        $this->events = $events;
    }

    /**
     * Start the giveaway.
     *
     * @param Channel $channel
     */
    public function start(Channel $channel)
    {
        $this->events->fire(new GiveAwayWasStarted($channel));

        return $this->redis->set(sprintf('giveaway:%d:started', $channel['id']), 1);
    }

    /**
     * Stop the giveaway.
     *
     * @param Channel $channel
     */
    public function stop(Channel $channel)
    {
        $this->events->fire(new GiveAwayWasStopped($channel));

        return $this->redis->set(sprintf('giveaway:%d:started', $channel['id']), 0);
    }

    /**
     * Reset the giveaway.
     *
     * @param Channel $channel
     */
    public function reset(Channel $channel)
    {
        $entries = $this->entries($channel, true);

        foreach ($entries as $entry) {
            $this->chatterRepo->setGiveAwayStatus($channel, $entry['handle'], false);
        }

        $this->stop($channel);
        $this->redis->del(sprintf('giveaway:%d', $channel['id']));

        $this->events->fire(new GiveAwayWasReset($channel));

        return true;
    }

    /**
     * Get the giveaway entries.
     *
     * @param Channel $channel
     * @param bool|false $grouped Should be group the entries bye handle?
     * @return array|Collection
     */
    public function entries(Channel $channel, $grouped = false)
    {
        $entries = $this->redis->lrange(sprintf('giveaway:%d', $channel['id']), 0, -1);

        if ($grouped === false) {
            return $entries;
        }

        $groups = [];
        foreach ($entries as $entry) {
            if ( ! array_key_exists($entry, $groups)) {
                $groups[$entry] = [
                    'handle' => $entry,
                    'tickets' => 1
                ];
            } else {
                $groups[$entry]['tickets'] += 1;
            }
        }

        return collect($groups);
    }

    /**
     * Select a winner from the list of entries.
     *
     * @param Channel $channel
     * @param bool $removeWinner Should the winner be removed from the entries?
     * @throws GiveAwayException
     */
    public function selectWinner(Channel $channel, $removeWinner = true)
    {
        $entries = $this->entries($channel);

        if (empty($entries)) {
            throw new GiveAwayException('There are no entries.');
        }

        $keys = array_keys($entries);
        shuffle($keys);

        $entries = array_combine($keys, $entries);
        ksort($entries);

        $winner = $entries[array_rand($entries)];

        if ($removeWinner) {
            $this->redis->lrem('giveaway:2', 0, $winner);
            $this->chatterRepo->setGiveAwayStatus($channel, $winner, false);
        }

        return $winner;
    }

    /**
     * @param Channel $channel
     * @return bool
     */
    public function isGiveAwayRunning(Channel $channel)
    {
        return (bool) $this->redis->get(sprintf('giveaway:%d:started', $channel['id']));
    }

    /**
     * @param Channel $channel
     * @param $handle
     * @return bool
     */
    public function checkIfEntered($viewer)
    {
        return $viewer['giveaway'];
    }

    /**
     * @param Channel $channel
     * @param $tickets
     * @return int
     */
    public function calculateCost(Channel $channel, $tickets)
    {
        return $channel->getSetting('giveaway.ticket-cost', 0) * $tickets;
    }

    /**
     * @param Entry $entry
     * @return string
     * @throws GiveAwayException
     */
    public function enter(Entry $entry)
    {
        if ( ! $this->isGiveAwayRunning($entry->getChannel())) {
            throw new GiveAwayException('Giveaway is not running.');
        }

        $ticketMax = $entry->getChannel()->getSetting('giveaway.ticket-max');

        if ($entry->getTickets() > $ticketMax) {
            throw new InvalidArgumentException(sprintf('%s: %d ticket max.', $entry->getHandle(), $ticketMax));
        }

        if ($entry->getTickets() < 1) {
            throw new InvalidArgumentException('Missing ticket amount.');
        }

        try {
            $viewer = $this->currencyManager->getViewer($entry->getChannel(), $entry->getHandle());
        } catch (UnknownHandleException $e) {
            $viewer = [
                'points' => 0,
                'giveaway' => false
            ];
        }

        if ($this->checkIfEntered($viewer)) {
            throw new GiveAwayException(sprintf('%s has already entered the giveaway.', $viewer['handle']));
        }

        $cost = $this->calculateCost($entry->getChannel(), $entry->getTickets());

        if ($cost > $viewer['points']) {
            throw new GiveAwayException(sprintf('%s does not have enough %s', $viewer['handle'], $entry->getChannel()->getSetting('currency.name')));
        }

        $this->chatterRepo->setGiveAwayStatus($entry->getChannel(), $entry->getHandle(), true);
        $this->currencyManager->remove($entry->getChannel(), $entry->getChannel()->name, $entry->getHandle(), $cost);

        for ($i = 0; $i < $entry->getTickets(); $i++) {
            $this->redis->lpush('giveaway:2', $entry->getHandle());
        }

        $this->events->fire(new GiveAwayWasEntered($entry->getChannel(), $entry->getHandle(), $entry->getTickets()));

        return true;
    }
}
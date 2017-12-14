<?php

namespace App\Giveaway;

use App\Channel;
use App\Contracts\Repositories\ChatterRepository;
use App\Currency\Manager as CurrencyManager;
use App\Events\Giveaway\GiveawayWasEntered;
use App\Events\Giveaway\GiveawayWasCleared;
use App\Events\Giveaway\GiveawayWasStarted;
use App\Events\Giveaway\GiveawayWasStopped;
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
        $this->events->fire(new GiveawayWasStarted($channel));

        return $this->redis->set("{$channel->id}:giveaway:started", 1);
    }

    /**
     * Stop the giveaway.
     *
     * @param Channel $channel
     */
    public function stop(Channel $channel)
    {
        $this->events->fire(new GiveawayWasStopped($channel));

        return $this->redis->set("{$channel->id}:giveaway:started", 0);
    }

    /**
     * Reset the giveaway.
     *
     * @param Channel $channel
     */
    public function clear(Channel $channel)
    {
        $entries = $this->entries($channel, true);

        $this->redis->del("{$channel->id}:giveaway:entries");

        $this->events->fire(new GiveawayWasCleared($channel));

        return true;
    }

    /**
     * Get the giveaway entries.
     *
     * @param Channel $channel
     * @param bool|false $grouped Should group the entries bye handle?
     * @return array|Collection
     */
    public function entries(Channel $channel, $grouped = false)
    {
        $entries = $this->redis->smembers("{$channel->id}:giveaway:entries");

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
            $this->redis->srem("{$channel->id}:giveaway:entries", $winner);
        }

        return $winner;
    }

    /**
     * @param Channel $channel
     * @return bool
     */
    public function isGiveAwayRunning(Channel $channel)
    {
        return (bool) $this->redis->get("{$channel->id}:giveaway:started");
    }

    /**
     * @param Channel $channel
     * @param $entry
     * @return bool
     */
    public function checkIfEntered($entry)
    {
        return (bool) $this->redis->sismember("{$entry->getChannel()->id}:giveaway:entries", $entry->getUser()['username']);
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
     * @throws InvalidHandleException
     */
    public function enter(Entry $entry)
    {
        if ( ! $this->isGiveAwayRunning($entry->getChannel())) {
            throw new GiveAwayException('Giveaway is not running.');
        }

        $ticketMax = $entry->getChannel()->getSetting('giveaway.ticket-max');
        $useTickets = $entry->getChannel()->getSetting('giveaway.use-tickets');
        $tickets = $entry->getTickets();

        if (! $entry->getUser()) {
            throw new InvalidArgumentException('Invalid Username.');
        }

        // if (! $this->validHandle($entry->getHandle())) {
        //     throw new UnknownHandleException('Handle may only contain alpha numeric characters, underscores and be between 2 and 25 characters in length.');
        // }

        if ($entry->getChannel()->getSetting('giveaway.use-tickets') === false) {
            $tickets = 1;
        }

        if ($tickets > $ticketMax) {
            throw new InvalidArgumentException(sprintf('%s: %d ticket max.', $entry->getUser()['display_name'], $ticketMax));
        }

        if ($tickets < 1) {
            throw new InvalidArgumentException('Missing ticket amount.');
        }

        if ($this->checkIfEntered($entry)) {
            throw new GiveAwayException(sprintf('%s has already entered the giveaway.', $entry->getUser()['display_name']));
        }

        if ($useTickets) {
            $viewer = $this->currencyManager->getViewer($entry->getChannel(), $entry->getUser()['twitch_id']);
            $cost = $this->calculateCost($entry->getChannel(), $tickets);

            if ($cost > $viewer['points']) {
                throw new GiveAwayException(sprintf('%s does not have enough %s.', $viewer['display_name'], strtolower($entry->getChannel()->getSetting('currency.name'))));
            }

            $this->currencyManager->remove($entry->getChannel(), $entry->getUser()['twitch_id'], $cost);
        }

        for ($i = 0; $i < $tickets; $i++) {
            $this->redis->sadd("{$entry->getChannel()->id}:giveaway:entries", $entry->getUser()['username']);
        }

        $this->events->fire(new GiveawayWasEntered($entry->getChannel(), $entry->getUser()['username'], $tickets));

        return true;
    }
}

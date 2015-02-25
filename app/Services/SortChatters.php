<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SortChatters {

    /**
     * Collection of chatters from the db.
     *
     * @var Collection
     */
    private $chatters;

    /**
     * Collection of chatters from twitch.
     *
     * @var Collection
     */
    private $liveChatters;

    /**
     * Collection of offline chatters.
     *
     * @var Collection
     */
    private $offlineChatters;

	/**
     * Collection of online chatters.
     *
     * @var Collection
     */
    private $onlineChatters;

	/**
     * Collection of new chatters.
     *
     * @var Collection
     */
    private $newChatters;

	/**
     * Has the sort been run.
     *
     * @var bool
     */
    private $sortHasRun = false;

    /**
     * @param Collection $liveChatters
     * @param Collection $chatters
     */
    public function __construct(Collection $liveChatters, Collection $chatters)
    {
        $this->chatters = $chatters;
        $this->liveChatters = $liveChatters;
        $this->offlineChatters = clone $chatters;
        $this->onlineChatters = new Collection();
        $this->newChatters = new Collection();
    }

	/**
     * Sort chatters users into one of three categories.
     * This method must be run before: onlineChatters,
     * newChatters, offlineChatters. If it's not run
     * manually it will be called automatically.
     *
     *  1. Online Chatters
     *  2. New Online Chatters
     *  3. Offline Chatters
     */
    public function sort()
    {
        foreach ($this->liveChatters as $chatter)
        {
            if (isset($this->chatters[$chatter]))
            {
                $this->onlineChatters->push($this->chatters[$chatter]);
            }
            else
            {
                $this->newChatters->push($chatter);
            }

            $this->offlineChatters->forget($chatter);
        }

        $this->sortHasRun = true;
    }

    /**
     * Online Chatters.
     *
     * To be considered online the user must be in the
     * chatters array & live chatters array.
     *
     * @return Collection
     */
    public function onlineChatters()
    {
        $this->ensureSortHasRun();

        return $this->onlineChatters;
    }

    /**
     * New Chatters
     *
     * If the user does not exist in the database they
     * are new.
     *
     * @return Collection
     */
    public function newChatters()
    {
        $this->ensureSortHasRun();

        return $this->newChatters;
    }

    /**
     * Offline Chatters
     *
     * To be considered offline the user must exist in the
     * chatters array but not in the live chatters array.
     *
     * @return Collection
     */
    public function offlineChatters()
    {
        $this->ensureSortHasRun();

        // Go through offline chatters and remove any
        // where the start_time is null.
        foreach($this->offlineChatters->all() as $chatter)
        {
            if ($chatter['start_time'] == null)
            {
                $this->offlineChatters->forget($chatter['handle']);
            }
        }

        return $this->offlineChatters;
    }

	/**
     * Ensure the sort method, if it has call it.
     */
    private function ensureSortHasRun()
    {
        if ( ! $this->sortHasRun)
        {
            $this->sort();
        }
    }

}
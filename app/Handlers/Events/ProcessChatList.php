<?php

namespace App\Handlers\Events;

use App\Contracts\Repositories\ChatterRepository;
use App\Events\VIPsWereUpdated;
use App\Events\ChatListWasDownloaded;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Events\Dispatcher;
use Carbon\Carbon;
use App\Channel;

class ProcessChatList
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var Dispatcher
     */
     private $events;

    /**
     * Create the event handler.
     *
     * @param ChatterRepository $chatterRepository
     * @param ConfigRepository $config
     */
    public function __construct(ChatterRepository $chatterRepository, ConfigRepository $config, Dispatcher $events)
    {
        $this->chatterRepository = $chatterRepository;
        $this->config = $config;
        $this->events = $events;
    }

    /**
     * Calculate how many minutes have gone by since the system
     * last run.
     *
     * @return int
     */
    private function calculateMinutes(Channel $channel)
    {
        $lastUpdate = getLastUpdate($channel);
        setLastUpdate($channel, Carbon::now());

        if ($lastUpdate instanceof Carbon) {
            return Carbon::now()->diffInMinutes($lastUpdate);
        }

        return 0;
    }

    /**
     * Calculate how many points the user has earned based
     * upon how many minutes they've been online.
     *
     * @param $minutes
     * @param $status
     *
     * @return float
     */
    private function calculatePoints(Channel $channel, $minutes, $status)
    {
        $status = $status === true ? 'online' : 'offline';

        $pointInterval = (int) $channel->getSetting('currency.interval', 0);
        $pointsAwarded = (int) $channel->getSetting('currency.awarded', 0);

        $pointsPerMinute = $pointsAwarded / $pointInterval;

        return round($pointsPerMinute * $minutes, 3);
    }

    /**
     * Handle the event.
     *
     * @param  ChatListWasDownloaded  $event
     * @return void
     */
    public function handle(ChatListWasDownloaded $event)
    {
        $minutes = $this->calculateMinutes($event->channel);
        $points = $this->calculatePoints($event->channel, $minutes, $event->status);

        $chattersList = $event->chatList['chatters'];
        $modList      = $event->chatList['moderators'];

        $this->chatterRepository->updateChatter($event->channel, $chattersList, $minutes, $points);
        $this->chatterRepository->updateModerator($event->channel, $modList, $minutes, $points);

        $this->events->fire(new VIPsWereUpdated($event->channel));
    }
}

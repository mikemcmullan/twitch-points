<?php

namespace App\Listeners;

use App\Contracts\Repositories\ChatterRepository;
use App\Events\VIPsWereUpdated;
use App\Events\ChatListWasDownloaded;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Events\Dispatcher;
use Carbon\Carbon;
use App\Channel;
use App\Support\ScoreboardCache;

class ProcessChatList
{
    /**
     * @var ChatterRepository
     */
    protected $chatterRepository;

    /**
     * @var ScoreboardCache
     */
    protected $scoreboardCache;

    /**
     * @var ConfigRepository
     */
    protected $config;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * Create the event handler.
     *
     * @param ChatterRepository $chatterRepository
     * @param ConfigRepository $config
     * @param Dispatcher $events
     * @param ScoreboardCache $scoreboardCache
     */
    public function __construct(ChatterRepository $chatterRepository, ConfigRepository $config, Dispatcher $events, ScoreboardCache $scoreboardCache)
    {
        $this->chatterRepository = $chatterRepository;
        $this->scoreboardCache = $scoreboardCache;
        $this->config = $config;
        $this->events = $events;
    }

    /**
     * Calculate how many minutes have gone by since the system
     * last run.
     *
     * @param Channel $channel
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
     * @param Channel $channel
     * @param $minutes
     * @param $status
     * @return float
     */
    private function calculatePoints(Channel $channel, $minutes, $status)
    {
        $status = $status === true ? 'online' : 'offline';

        $pointInterval = (int) $channel->getSetting("currency.{$status}-interval", 0);
        $pointsAwarded = (int) $channel->getSetting("currency.{$status}-awarded", 0);

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

        $chatters     = $this->chatterRepository->updateChatter($event->channel, $chattersList, $minutes, $points);
        $mods         = $this->chatterRepository->updateModerator($event->channel, $modList, $minutes, $points);

        foreach ($chatters['existing'] as $chatter) {
            $chatter['points'] += $chatters['points'];
            $chatter['minutes'] += $chatters['minutes'];

            if ($chatter['hidden'] === 0) {
                $this->scoreboardCache->addViewer($event->channel, $chatter);
            }
        }

        foreach ($mods['existing'] as $chatter) {
            $chatter['points'] += $chatters['points'];
            $chatter['minutes'] += $chatters['minutes'];

            if ($chatter['hidden'] === 0) {
                $this->scoreboardCache->addViewer($event->channel, $chatter);
            }
        }

        foreach ($chatters['new'] as $handle) {
            $chatter = [
                'handle'        => $handle['username'],
                'points'        => $chatters['points'],
                'minutes'       => $chatters['minutes'],
                'moderator'     => false
            ];

            $this->scoreboardCache->addViewer($event->channel, $chatter);
        }

        foreach ($mods['new'] as $handle) {
            $chatter = [
                'handle'        => $handle['username'],
                'points'        => $chatters['points'],
                'minutes'       => $chatters['minutes'],
                'moderator'     => true
            ];

            $this->scoreboardCache->addViewer($event->channel, $chatter);
        }

        $this->events->fire(new VIPsWereUpdated($event->channel));
    }
}

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
            $mins = Carbon::now()->diffInMinutes($lastUpdate);

            return $mins <= 10 ? $mins : 0;
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
     * Update the chatters.
     *
     * @param  Channel $channel
     * @param  array   $chatList
     * @param  int     $minutes
     * @param  float   $points
     * @return void
     */
    private function updateChatters(Channel $channel, $chatList, $minutes, $points)
    {
        $chatters = collect(array_merge($chatList['chatters'], $chatList['moderators']));
        $modIds = array_pluck($chatList['moderators'], 'twitch_id');


        $existingChatters = $this->chatterRepository
            ->findByTwitchId($channel, array_pluck($chatters, 'twitch_id'))
            ->map(function ($chatter) use ($chatters) {
                $new = $chatters->where('twitch_id', $chatter['twitch_id']);

                if (! $new->isEmpty()) {
                    $chatter['username'] = $new->first()['username'];
                    $chatter['display_name'] = $new->first()['display_name'];
                }

                return $chatter;
            });

        $newChatters = collect($chatList['chatters'])->filter(function ($chatter) use ($existingChatters) {
            return $existingChatters->where('twitch_id', $chatter['twitch_id'])->count() === 0;
        });

        $this->chatterRepository->newChatters($channel, $newChatters, $minutes, $points);
        $this->chatterRepository->updateChatters($channel, $existingChatters, $minutes, $points);

        foreach ($modIds as $modId) {
            $this->chatterRepository->addMod($channel, $modId);
        }

        foreach ($existingChatters as $chatter) {
            $chatter['points'] += $points;
            $chatter['minutes'] += $minutes;

            if ($chatter['hidden'] === 0) {
                $this->scoreboardCache->addViewer($channel, $chatter);
            }
        }
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

        $doublePoints = (bool) $event->channel->getSetting('currency.active-chatters-double', false);
        $onlyActive = (bool) $event->channel->getSetting('currency.only-active-chatters', false);
        $activePoints = $points;

        if (!$onlyActive && $doublePoints) {
            $activePoints = $points*2;
        }

        $this->updateChatters($event->channel, $event->chatList['active'], $minutes, $doublePoints ? $points*2 : $points);

        if ($onlyActive === false) {
            $this->updateChatters($event->channel, $event->chatList['online'], $minutes, $points);
        }

        $this->events->fire(new VIPsWereUpdated($event->channel));
    }
}

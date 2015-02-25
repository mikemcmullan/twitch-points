<?php

namespace App\Services;

use App\Repositories\Chatters\ChatterRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;

class UpdateDBChatters {

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var ChatUserRepository
     */
    private $chatterRepository;

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     * @param ConfigRepository $config
     * @param ChatterRepository $chatterRepository
     */
    public function __construct(User $user, ConfigRepository $config, ChatterRepository $chatterRepository)
    {
        $this->config = $config;
        $this->chatterRepository = $chatterRepository;
        $this->user = $user;
    }

    /**
     * Save new users to the DB.
     *
     * @param Collection $users
     */
    public function newChatters(Collection $users)
    {
        return $this->chatterRepository->createMany($this->user, $users);
    }

    /**
     * Update users who are still online.
     *
     * @param Collection $users
     */
    public function onlineChatters(Collection $users)
    {
        $onlineUsers = new Collection();

        foreach ($users as $user)
        {
            $minutesOnline = 0;

            if ($user['start_time'] != null)
            {
                $minutesOnline = Carbon::now()->diffInMinutes(Carbon::parse($user['start_time']));
            }

            $user['points'] = $this->calculatePoints($minutesOnline);
            $user['total_minutes_online'] = $minutesOnline;

            $onlineUsers->push($user);
        }

        return $this->chatterRepository->updateMany($this->user, $onlineUsers);
    }

    /**
     * Set offline users to offline.
     *
     * @param Collection $users
     */
    public function offlineChatters(Collection $users)
    {
        return $this->chatterRepository->offlineMany($this->user, $users);
    }

    /**
     * Set all users to offline by setting their start_time to null.
     *
     * @return bool
     */
    public function setAllChattersOffline()
    {
        return $this->chatterRepository->offlineAllForChannel($this->user);
    }

    /**
     * Calculate how many points to award a person for watching.
     *
     * @param int $minutesOnline
     * @return float
     */
    private function calculatePoints($minutesOnline = 0)
    {
        $pointInterval = $this->config->get('twitch.points.interval');
        $pointsAwarded = $this->config->get('twitch.points.awarded');

        $pointsPerMinute = $pointsAwarded / $pointInterval;

        return round($pointsPerMinute * $minutesOnline, 3);
    }
}
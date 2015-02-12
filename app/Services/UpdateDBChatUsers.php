<?php

namespace App\Services;

use App\Repositories\ChatUsers\ChatUserRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;

class UpdateDBChatUsers {

    /**
     * @var String
     */
    private $channel;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var ChatUserRepository
     */
    private $chatUserRepository;

    /**
     * @param $channel
     * @param ConfigRepository $config
     * @param ChatUserRepository $chatUserRepository
     */
    public function __construct($channel, ConfigRepository $config, ChatUserRepository $chatUserRepository)
    {
        $this->channel = $channel;
        $this->config = $config;
        $this->chatUserRepository = $chatUserRepository;
    }

    /**
     * Save new users to the DB.
     *
     * @param Collection $users
     */
    public function newOnlineUsers(Collection $users)
    {
        return $this->chatUserRepository->createMany($this->channel, $users);
    }

    /**
     * Update users who are still online.
     *
     * @param Collection $users
     */
    public function onlineUsers(Collection $users)
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

        return $this->chatUserRepository->updateMany($this->channel, $onlineUsers);
    }

    /**
     * Set offline users to offline.
     *
     * @param Collection $users
     */
    public function offlineUsers(Collection $users)
    {
        return $this->chatUserRepository->offlineMany($this->channel, $users);
    }

    /**
     * Set all users to offline by setting their start_time to null.
     *
     * @param Collection $users
     */
    public function setAllUsersOffline(Collection $users)
    {
        return $this->offlineUsers($users);
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
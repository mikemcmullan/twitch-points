<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Repositories\Chatter\MySQLChatterRepository;
use App\Repositories\Chatter\RedisChatterRepository;
use App\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MigrateRedisToMySQL extends Job
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RedisChatterRepository $redisRepo, MySQLChatterRepository $mysqlRepo)
    {
        $chatters = $redisRepo->allForChannel($this->channel, true, true);
        $admins = $redisRepo->allAdminsForChannel($this->channel);

        $chatters->each(function ($chatter) use ($mysqlRepo) {
            if ($chatter['mod'] === true) {
                $mysqlRepo->updateModerator($this->channel, $chatter['handle'], $chatter['minutes'], $chatter['points']);
            } else {
                $mysqlRepo->updateChatter($this->channel, $chatter['handle'], $chatter['minutes'], $chatter['points']);
            }
        });

        $admins->each(function ($admin) use ($mysqlRepo) {
            $mysqlRepo->addAdmin($this->channel, $admin['handle']);
        });
    }
}

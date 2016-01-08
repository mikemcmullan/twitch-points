<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use App\Contracts\Repositories\ChatterRepository;
use App\Channel;

class RankChattersJob extends Job
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * Create a new job instance.
     *
     * @param $channel
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
    public function handle(ChatterRepository $chatterRepository)
    {
        $chatters = $chatterRepository->allForChannel($this->channel, false, $this->channel->getSetting('rank-mods'));
        $rankings = new Collection();
        $rank = 1;

        $groups = $chatters->groupBy('points');

        foreach ($groups as $group) {
            foreach ($group as $chatter) {
                $chatter['rank'] = $rank;
                $rankings->push($chatter);
            }

            $rank++;
        }

        $chatterRepository->updateRankings($this->channel, $rankings->toArray());
    }
}

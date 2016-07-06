<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\ChatterRepository;
use App\Support\ScoreboardCache;

class UpdateScoreboardCache extends Job
{
    /**
     * @var Channel
     */
    protected $channel;

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
     * @param  ChatterRepository $chatterRepo
     * @param  ScoreboardCache   $scoreboardCache
     */
    public function handle(ChatterRepository $chatterRepo, ScoreboardCache $scoreboardCache)
    {
        $chatters = $chatterRepo->allForChannel($this->channel, false, $this->channel->getSetting('rank-mods', false));
        $scoreboardCache->clear($this->channel);

        foreach ($chatters as $chatter) {
            $scoreboardCache->addViewer($this->channel, $chatter);
        }
    }
}

<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\ChatterRepository;

class RemoveChannelJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

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
    public function handle(ChatterRepository $chatterRepo)
    {
        $viewers = $chatterRepo->allForChannel($this->channel, true, true);

        foreach ($viewers as $viewer) {
            $chatterRepo->deleteChatter($this->channel, $viewer['handle']);
        }

        $chatterRepo->deleteChannel($this->channel);

        $users = $this->channel->users->lists('id')->toArray();
        $this->channel->users()->detach($users);

        $this->channel->trackSessions()->delete();
        $this->channel->commands()->delete();
        $this->channel->delete();
    }
}

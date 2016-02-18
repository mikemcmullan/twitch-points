<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\ChatterRepository;
use App\Channel;
use Carbon\Carbon;

class RemoveOldViewersJob extends Job
{
    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var
     */
    public $points;

    /**
     * @var
     */
    public $days;

    /**
     * Create a new job instance.
     *
     * @param Channel $channel
     * @param int $days             How many days must a viewer be inactive before being deleted.
     * @param int $points           Only delete if they have less than [x] amount of points.
     */
    public function __construct(Channel $channel, $days = 0, $points = 0)
    {
        $this->channel = $channel;
        $this->points = $points;
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @param  RemoveOldViewersCommand  $command
     * @return int The amount of chatter that were deleted.
     */
    public function handle(ChatterRepository $chatterRepository)
    {
        $viewers = $chatterRepository->allForChannel($this->channel);
        $now = Carbon::now();
        $deleteCount = 0;

        $toDelete = $viewers->filter(function ($viewer) use ($now) {
            return Carbon::parse($viewer['updated'])->diffInDays($now) >= (int) $this->days && $viewer['points'] <= (int) $this->points;
        });

        $toDelete->each(function ($viewer) use ($chatterRepository, &$deleteCount) {
            $chatterRepository->deleteChatter($this->channel, $viewer['handle']);
            $deleteCount++;
        });

        return $deleteCount;
    }
}

<?php

namespace App\Handlers\Commands;

use App\Commands\RemoveOldViewersCommand;
use App\Contracts\Repositories\ChatterRepository;
use Carbon\Carbon;

class RemoveOldViewersCommandHandler
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * Create the command handler.
     *
     * @param ChatterRepository $chatterRepository
     */
    public function __construct(ChatterRepository $chatterRepository)
    {
        $this->chatterRepository = $chatterRepository;
    }

    /**
     * Handle the command.
     *
     * @param  RemoveOldViewersCommand  $command
     * @return void
     */
    public function handle(RemoveOldViewersCommand $command)
    {
        $viewers = $this->chatterRepository->allForChannel($command->channel);
        $now = Carbon::now();

        $toDelete = $viewers->filter(function ($viewer) use ($now, $command) {
            return Carbon::parse($viewer['updated'])->diffInDays($now) >= (int) $command->days && $viewer['points'] <= (int) $command->points;
        });

        $toDelete->each(function ($viewer) use ($command) {
            $this->chatterRepository->deleteChatter($command->channel, $viewer['handle']);
        });
    }
}

<?php

namespace App\Handlers\Commands;

use App\Commands\RankChattersCommand;
use App\Contracts\Repositories\ChatterRepository;
use Illuminate\Database\Eloquent\Collection;

class RankChattersCommandHandler
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
     * @param  RankChattersCommand  $command
     * @return void
     */
    public function handle(RankChattersCommand $command)
    {
        $chatters = $this->chatterRepository->allForChannel($command->channel, false, $command->channel->rank_mods);
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

        $this->chatterRepository->updateRankings($command->channel, $rankings->toArray());
    }
}

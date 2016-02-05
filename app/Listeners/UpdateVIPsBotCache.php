<?php

namespace App\Listeners;

use App\Events\VIPSWereUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Redis\Database;
use App\Contracts\Repositories\ChatterRepository;

class UpdateVIPsBotCache
{
    /**
     * @var string
     */
    private $vipsKey = 'cacheman:#%s:vips';

    /*
     * @var ChatterRepository
     */
    private $chatterRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Database $redis, ChatterRepository $chatterRepo)
    {
        $this->redis = $redis;
        $this->chatterRepo = $chatterRepo;
    }
    /**
     * Handle the event.
     *
     * @param  VIPSWereUpdated  $event
     * @return void
     */
    public function handle(VIPSWereUpdated $event)
    {
        $mods = $this->chatterRepo->allModsForChannel($event->channel);
        $admins = $this->chatterRepo->allAdminsForChannel($event->channel);

        $this->redis->set(sprintf($this->vipsKey, $event->channel->name), json_encode([
            'owner' => [$event->channel->name],
            'admins' => $admins->keys(),
            'mods' => $mods->keys()
        ]));
    }
}

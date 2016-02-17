<?php

namespace App\Listeners;

use App\Events\ChatListWasDownloaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Contracts\Repositories\ChatterRepository;

class UpdateDeModdedChatters
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ChatterRepository $chatterRepository)
    {
        $this->chatterRepository = $chatterRepository;
    }

    /**
     * Handle the event.
     *
     * @param  ChatListWasDownloaded  $event
     * @return void
     */
    public function handle(ChatListWasDownloaded $event)
    {
        $currentMods = $this->chatterRepository->allModsForChannel($event->channel);

        $demmoded = array_filter($event->chatList['chatters'], function ($chatter) use ($currentMods) {
            return $currentMods->get($chatter);
        });

        // \Log::info($event->chatList);
        // \Log::info($demmoded);

        foreach ($demmoded as $handle) {
            $this->chatterRepository->removeMod($event->channel, $handle);
        }
    }
}

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
        $chatters = array_merge($event->chatList['active']['chatters'], $event->chatList['online']['chatters']);
        $demodded = [];

        foreach ($chatters as $chatter) {
            if ($currentMods->get($chatter['twitch_id'])) {
                $demodded[] = $chatter;
            }
        }

        foreach ($demodded as $chatter) {
            $this->chatterRepository->removeMod($event->channel, $chatter['twitch_id']);
        }
    }
}

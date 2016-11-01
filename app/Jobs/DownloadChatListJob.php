<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ChatListWasDownloaded;
use App\Exceptions\InvalidChannelException;
use App\Services\TwitchApi;
use Illuminate\Events\Dispatcher;
use App\Channel;
use App\Contracts\Repositories\TrackSessionRepository;

class DownloadChatListJob extends Job
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
     * @param  TwitchApi $twitchApi
     * @param Dispatcher $events
     * @param TrackSessionRepository $trackSessionRepo
     * @return array
     */
     public function handle(TwitchApi $twitchApi, Dispatcher $events, TrackSessionRepository $trackSessionRepo)
     {
         $status = (bool) $trackSessionRepo->findIncompletedSession($this->channel);
         $offlineAwarded = (int)  $this->channel->getSetting('currency.offline-awarded', 0);

         if ($status === false && $offlineAwarded === 0) {
             return [];
         }

         $chatList   = $twitchApi->chatList($this->channel['name']);

         $events->fire(new ChatListWasDownloaded($this->channel, $chatList, $status));

         return $chatList;
     }
}

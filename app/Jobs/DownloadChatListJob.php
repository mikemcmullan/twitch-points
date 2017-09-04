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
use App\Contracts\Repositories\StreamRepository;
use App\Support\ActiveChatters;

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
     * @param StreamRepository $streamRepo
     * @return array
     */
     public function handle(TwitchApi $twitchApi, Dispatcher $events, StreamRepository $streamRepo, ActiveChatters $activeChatters)
     {
         $status = (bool) $streamRepo->findIncompletedStream($this->channel);
         $offlineAwarded = (int) $this->channel->getSetting('currency.offline-awarded', 0);

         if ($status === false && $offlineAwarded === 0) {
             return [];
         }

         if ($this->channel->getSetting('currency.source', 'tmi') === 'tmi') {
             $chatList = $twitchApi->chatList($this->channel['name']);
         } else {
             $chatList = $activeChatters->get($this->channel);
         }

         $events->fire(new ChatListWasDownloaded($this->channel, $chatList, $status));

         return $chatList;
     }
}

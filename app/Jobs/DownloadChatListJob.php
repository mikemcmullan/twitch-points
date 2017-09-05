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
     * @var array
     */
    protected $activeChatters = [];

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
      * Test if a name is an active chatter.
      *
      * @param  string $name
      * @return bool
      */
     public function filterActiveChatters($name)
     {
         return in_array($name, $this->activeChatters['chatters']);
     }

     /**
      * Test if a name is an active moderator.
      *
      * @param  string $name
      * @return bool
      */
     public function filterActiveModerators($name)
     {
         return in_array($name, $this->activeChatters['moderators']);
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
         $status            = (bool) $streamRepo->findIncompletedStream($this->channel);
         $offlineAwarded    = (int) $this->channel->getSetting('currency.offline-awarded', 0);

         if ($status === false && $offlineAwarded === 0) {
             return [];
         }

         $chatList = $twitchApi->chatList($this->channel['name']);
         $this->activeChatters = $activeChatters->get($this->channel);

         if ($this->channel->getSetting('currency.only-active-chatters', false) === true) {
             $chatList['moderators'] = array_filter($chatList['moderators'], [$this, 'filterActiveModerators']);
             $chatList['chatters']   = array_filter($chatList['chatters'], [$this, 'filterActiveChatters']);
         }

         $events->fire(new ChatListWasDownloaded($this->channel, $chatList, $status));

         return $chatList;
     }
}

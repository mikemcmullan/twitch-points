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
         $activeChatters = $activeChatters->get($this->channel);

         $online = ['moderators' => [], 'chatters' => []];
         $active = ['moderators' => [], 'chatters' => []];

         foreach ($chatList['moderators'] as $username) {
             if (in_array($username, $activeChatters['moderators'])) {
                 $active['moderators'][] = $username;
             } else {
                 $online['moderators'][] = $username;
             }
         }

         foreach ($chatList['chatters'] as $username) {
             if (in_array($username, $activeChatters['chatters'])) {
                 $active['chatters'][] = $username;
             } else {
                 $online['chatters'][] = $username;
             }
         }

         $return = [
             'online' => $online,
             'active' => $active
         ];

         $events->fire(new ChatListWasDownloaded($this->channel, $return, $status));

         return $return;
     }
}

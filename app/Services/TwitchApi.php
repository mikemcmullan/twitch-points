<?php

namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class TwitchApi {

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * @param CacheRepository $cache
     */
    public function __construct(CacheRepository $cache)
    {
        $this->httpClient = $this->setupHttpClient();
        $this->cache = $cache;
    }

    /**
     * @return Client
     */
    private function setupHttpClient()
    {
        $client = new Client([
            'defaults' => [
                'headers' => [
                    'Accept' => 'application/vnd.twitchtv.v3+json'
                ]
            ]
        ]);

        return $client;
    }

    /**
     * Get the chat user list from twitch.
     *
     * @param $channel
     * @return \Illuminate\Support\Collection
     */
    public function chatList($channel)
    {
//        return $this->cache->remember('chatList:' . $channel, 10, function() use($channel)
//        {
            $response = $this->httpClient->get(sprintf('https://tmi.twitch.tv/group/user/%s/chatters', $channel));

            return $this->parseChatList((string) $response->getBody());
//        });
    }

    /**
     * Parse the json chat list.
     *
     * @param $jsonString
     * @return \Illuminate\Support\Collection
     */
    private function parseChatList($jsonString)
    {
        $json = json_decode($jsonString, true);

        return collect(array_merge(
            $json['chatters']['moderators'],
            $json['chatters']['staff'],
            $json['chatters']['admins'],
            $json['chatters']['global_mods'],
            $json['chatters']['viewers']
        ));
    }

    /**
     * Check if a channel exists.
     *
     * @param $channelName
     * @return bool
     */
    public function validChannel($channelName)
    {
        return $this->getStream($channelName);
    }

    /**
     * Check if channel is online.
     *
     * @param $channelName
     * @return bool
     */
    public function channelOnline($channelName)
    {
        $stream = $this->getStream($channelName);

        if ($stream && $stream['stream'] != null)
        {
            return true;
        }

        return false;
    }

    /**
     * Fetch channel info from the api.
     *
     * @param $channel
     * @return mixed
     */
    public function getStream($channel)
    {
        return $this->cache->remember('valid:' . $channel, 1, function() use($channel)
        {
            try
            {
                $response = $this->httpClient->get('https://api.twitch.tv/kraken/streams/' . $channel);
                return json_decode((string) $response->getBody(), true);
            }
            catch(ClientException $e)
            {
                return false;
            }
        });
    }
}
<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Log\Writer;
use App\Exceptions\InvalidChannelException;

class TwitchApi
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var CacheRepository
     */
    private $cache;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var Writer
     */
    private $logger;

    /**
     * @param CacheRepository $cache
     */
    public function __construct(CacheRepository $cache, ConfigRepository $config, Writer $logger)
    {
        $this->httpClient = $this->setupHttpClient();
        $this->cache = $cache;
        $this->config = $config;
        $this->logger = $logger;
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
     * @return array
     */
    public function chatList($channel)
    {
        $this->getStream($channel);

        $attempts = 1;
        $stop = false;

        // Sometimes the tmi server returns an error, we'll try multiple times
        // before giving up.
        $this->logger->info('Trying to get chat list.', ['channel' => $channel]);

        while ($stop === false) {
            try {
                $this->logger->info(sprintf('Attempt: #%d', $attempts), ['channel' => $channel]);
                $response = $this->httpClient->get(sprintf($this->config->get('twitch.chat_list_api'), $channel));
                $this->logger->info(sprintf('Chat list was obtained, took %d attempts.', $attempts), ['channel' => $channel]);
                $stop = true;
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                if ($attempts > 5) {
                    $this->logger->error('Failed to get chat list.', ['channel' => $channel]);
                    $stop = true;
                }

                sleep(0.5);
                $attempts += 1;
            }
        }

        return $this->parseChatList((string) $response->getBody());
    }

    /**
     * Parse the json chat list.
     *
     * @param $jsonString
     * @return array
     */
    private function parseChatList($jsonString)
    {
        $json = json_decode($jsonString, true);

        return [
            'chatters' => array_merge(
                $json['chatters']['staff'],
                $json['chatters']['admins'],
                $json['chatters']['global_mods'],
                $json['chatters']['viewers']
            ),

            'moderators' => $json['chatters']['moderators']
        ];
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

        if ($stream && $stream['stream'] != null) {
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
        try {
            $response = $this->httpClient->get('https://api.twitch.tv/kraken/streams/' . $channel);
            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            $this->logger->error('Invalid channel.', ['channel' => $channel]);
            throw new InvalidChannelException($channel);
        } catch (ServerException $e) {
            $this->logger->error('Unable to get stream, twitch api error.');
            throw new \Exception('Unable to get stream, twitch api error.');
        }
    }
}

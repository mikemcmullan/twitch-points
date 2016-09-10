<?php

namespace App\EmoteReplacer\Replacers;

use App\Channel;
use GuzzleHttp\Client;

class BetterTTV extends Replacer
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $globalEmoteTemplate = '';

    /**
     * @var string
     */
    protected $channelEmoteTemplate = '';

    /**
     * @var array
     */
    protected $globalEmotes = [];

    /**
     * @var array
     */
    protected $channelEmotes = [];

    /**
     * @var array
     */
    public $emoteCount = [];

    /**
     * @param Channel|null $channel If no channel is provided then no channel
     *                              bttv emotes will be used.
     */
    public function __construct(Channel $channel = null)
    {
        $this->channel = $channel;

        $this->getGlobalEmotes();

        if ($channel) {
            $this->getChannelEmotes($channel);
        }
    }

    /**
     * Get emotes and cache them.
     *
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    protected function getEmotes($url)
    {
        return \Cache::remember('bttvEmotes-' . md5($url), 30, function () use ($url) {
            $http = new Client();

            $response = $http->request('GET', $url);
            $response = json_decode((string) $response->getBody());
            $urlTemplate = '';
            $emotes = [];

            if ($response->status === 200) {
                $urlTemplate = $response->urlTemplate;
                $emotes = $response->emotes;
            }

            return [$urlTemplate, $emotes];
        });
    }

    /**
     * Get bttv global emotes.
     *
     * @return array            An array of emotes.
     */
    protected function getGlobalEmotes()
    {
        $response = $this->getEmotes('https://api.betterttv.net/2/emotes');

        $this->globalEmoteTemplate = $response[0];
        $this->globalEmotes = $response[1];

        return $response;
    }

    /**
     * Get bttv emotes for a channel.
     *
     * @param  Channel $channel
     * @return array            An array of emotes.
     */
    protected function getChannelEmotes(Channel $channel)
    {
        try {
            $response = $this->getEmotes("https://api.betterttv.net/2/channels/{$channel->name}");
        } catch (\Exception $e) {
            $response = ['', []];
        }

        $this->channelEmoteTemplate = $response[0];
        $this->channelEmotes = $response[1];

        return $response;
    }

    /**
     * Find and replace emotes with images in a message.
     *
     * @param  Object  $message   Object containing message.
     * @param  integer $imageSize The size of emote, 1x, 2x or 3x
     * @return string
     */
    public function replace($message, $imageSize = '1x')
    {
        $messageStr = $message->message;

        foreach ($this->globalEmotes as $emote) {
            $messageStr = str_replace($emote->code, $this->makeImage($emote->id, $imageSize, $this->globalEmoteTemplate), $messageStr, $replacementCount);

            if ($replacementCount > 0) {
                $this->emoteCount[$emote->id] = $replacementCount;
            }
        }

        foreach ($this->channelEmotes as $emote) {
            $messageStr = str_replace($emote->code, $this->makeImage($emote->id, $imageSize, $this->channelEmoteTemplate), $messageStr, $replacementCount);

            if ($replacementCount > 0) {
                $this->emoteCount[$emote->id] = $replacementCount;
            }
        }

        return $messageStr;
    }
}

<?php

namespace App\EmoteReplacer\Replacers;

class Twitch extends Replacer
{
    public $emoteTemplate = '//static-cdn.jtvnw.net/emoticons/v1/{{id}}/{{image}}';

    /**
     * Using the emote string find the characters that need to be replaced with
     * emote images.
     * @param  [type] $messageStr  [description]
     * @param  [type] $emoteString [description]
     * @return [type]              [description]
     */
    protected function findEmote($messageStr, $emoteString)
    {
        $emotes = [];

        list($id, $positions) = explode(':', $emoteString);

        $positions = array_map(function ($part) {
            return explode('-', $part);
        }, explode(',', $positions));

        foreach ($positions as $pos) {
            array_push($emotes, [$id, substr($messageStr, $pos[0], intval($pos[1]) - intval($pos[0]) + 1)]);
        }

        return $emotes;
    }

    /**
     * Find and replace emotes with images in a message.
     *
     * @param  Object  $message   Object containing message and emotes properties.
     * @param  integer $imageSize The size of emote, 1.0, 2.0, 3.0 or 4.0
     * @return string
     */
    public function replace($message, $imageSize = '1.0')
    {
        if (! $message->emotes) {
            return $message->message;
        }

        $messageStr = $message->message;
        $emotes = array_map(function ($emoteString) use ($messageStr) {
            return $this->findEmote($messageStr, $emoteString);
        }, explode('/', $message->emotes));

        foreach ($emotes as $emote) {
            foreach ($emote as $em) {
                $messageStr = str_replace($em[1], $this->makeImage($em[0], $imageSize, $this->emoteTemplate), $messageStr);
            }
        }

        return $messageStr;
    }
}

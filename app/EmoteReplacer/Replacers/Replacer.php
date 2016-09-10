<?php

namespace App\EmoteReplacer\Replacers;

abstract class Replacer
{
    /**
     * Make the url for an emote.
     *
     * @param  integer  $imageId       The id of the emote.
     * @param  integer  $imageSize     The size of emote, 1, 2, 3 or 4
     * @param  string   $urlTemplate   The emote url template.
     * @return string
     */
    protected function formateUrl($imageId, $imageSize, $urlTemplate)
    {
        return str_replace(['{{id}}', '{{image}}'], [$imageId, $imageSize], $urlTemplate);
    }

    /**
     * Make the image tag for the emote.
     *
     * @param  integer  $imageId       The id of the emote.
     * @param  integer  $imageSize     The size of emote, 1, 2, 3 or 4
     * @param  string   $urlTemplate   The emote url template.
     * @return string
     */
    protected function makeImage($imageId, $imageSize, $urlTemplate)
    {
        return ' <img src="' . $this->formateUrl($imageId, $imageSize, $urlTemplate) . '"> ';
    }

}

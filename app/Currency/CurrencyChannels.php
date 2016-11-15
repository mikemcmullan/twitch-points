<?php

namespace App\Currency;

use App\Channel;

trait CurrencyChannels
{
    public function getChannel($name)
    {
        return Channel::findByName($name);
    }

    public function getActiveCurrencyChannels()
    {
        $channels = Channel::all()->filter(function ($channel) {
            return $channel->getSetting('currency.status', false) == true;
        });

        return $channels;
    }
}

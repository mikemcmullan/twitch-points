<?php

namespace App\Currency;

trait CurrencyChannels
{
    public function getActiveCurrencyChannels()
    {
        $channels = \App\Channel::all()->filter(function ($channel) {
            return $channel->getSetting('currency.status', false) == true;
        });

        return $channels;
    }
}

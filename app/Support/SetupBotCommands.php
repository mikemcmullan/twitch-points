<?php

namespace App\Support;

use App\Channel;
use App\SystemCommandOverrides;
use Illuminate\Config\Repository;
use DB;

class SetupBotCommands {

    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function setup(Channel $channel)
    {
        // A few commands need configuration. Instead of creating command overrides
        // for every channel and substitute in the user configured keyword.
        $this->config->set([
            'commands.system.currency.get.command' =>
                sprintf(config('commands.system.currency.get.command'), $channel->getSetting('currency.keyword', '!coins')),
            'commands.system.currency.get.usage' =>
                sprintf(config('commands.system.currency.get.usage'), $channel->getSetting('currency.keyword', '!coins')),

            'commands.system.giveaway.enter.command' =>
                sprintf(config('commands.system.giveaway.enter.command'), $channel->getSetting('giveaway.keyword', '!tickets')),
            'commands.system.giveaway.enter.usage' =>
                sprintf(config('commands.system.giveaway.enter.usage'), $channel->getSetting('giveaway.keyword', '!tickets')),

            'commands.system.misc.commands.response' =>
                sprintf(config('commands.system.misc.commands.response'), route('commands_path', $channel->slug))
        ]);

        // Go through all the commands and add a id and type.
        foreach ($this->config->get('commands.system', []) as $groupKey => $group) {
            foreach ($group as $key => $command) {
                $this->config->set([
                    'commands.system' . '.' . $groupKey . '.' . $key . '.id' => $groupKey . '.' . $key,
                    'commands.system' . '.' . $groupKey . '.' . $key . '.type' => 'system'
                ]);
            }
        }

        // Load command overrides from the database and set them in the config.
        $overrides = SystemCommandOverrides::where('channel_id', $channel->id)->get()->toArray();

        foreach ($overrides as $override) {
            if (trim($override['value']) === 'true') {
                $override['value'] = true;
            }

            if ($override['value'] === 'false') {
                $override['value'] = false;
            }

            if ($this->config->get('commands.system.' . $override['name']) !== null) {
                $this->config->set('commands.system.' . $override['name'], $override['value']);
            }
        }

    }

}

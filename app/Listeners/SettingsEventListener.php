<?php

namespace App\Listeners;
use App\BotCommands\Manager;
use App\Channel;
use App\Command;

class SettingsEventListener
{
    /**
     * @var Manager
     */
    private $commandsManager;

    /**
     * Constructor
     *
     * @param Manager $commandsManager
     */
    public function __construct(Manager $commandsManager)
    {
        $this->commandsManager = $commandsManager;
    }

    /**
     * Update the currency command when the currency keyword is updated in the settings.
     *
     * @param Channel $channel
     * @param string $oldSetting
     * @param string $newSetting
     *
     * @return void
     */
    public function currencyKeywordUpdated(Channel $channel, $oldSetting, $newSetting)
    {
        $command = Command::where(['channel_id' => $channel->id, 'file' => 'GetCurrency'])->first();

        if ($command) {
            $this->commandsManager->update($channel, $command->id, [
                'command' => sprintf(config('commands.default.currency.get.command'), preg_quote($newSetting, '/')),
                'usage' => sprintf(config('commands.default.currency.get.usage'), $newSetting)
            ]);
        }
    }

    /**
     * Update the giveaway command when the giveaway keyword is updated in the settings.
     *
     * @param Channel $channel
     * @param string $oldSetting
     * @param string $newSetting
     *
     * @return void
     */
    public function giveawayKeywordUpdated(Channel $channel, $oldSetting, $newSetting)
    {
        $command = \App\Command::where(['channel_id' => $channel->id, 'file' => 'Giveaway'])->first();

        if ($command) {
            $this->commandsManager->update($channel, $command->id, [
                'command' => sprintf(config('commands.default.giveaway.enter.command'), preg_quote($newSetting, '/')),
                'usage' => sprintf(config('commands.default.giveaway.enter.usage'), $newSetting)
            ]);
        }
    }

    /**
    * Register the listeners for the subscriber.
    *
    * @param  Illuminate\Events\Dispatcher  $events
    */
   public function subscribe($events)
   {
       $events->listen('settings.updated.currency.keyword', 'App\Listeners\SettingsEventListener@currencyKeywordUpdated');
       $events->listen('settings.updated.giveaway.keyword', 'App\Listeners\SettingsEventListener@giveawayKeywordUpdated');
   }
}

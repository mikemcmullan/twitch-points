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
        $commands = Command::where(['channel_id' => $channel->id, 'file' => 'GetCurrency'])->get();

        $commands->each(function ($command) use ($channel, $oldSetting, $newSetting) {
            $this->commandsManager->update($channel, $command->id, [
                'command' => str_replace($oldSetting, $newSetting, $command->command),
                'usage' => str_replace($oldSetting, $newSetting, $command->usage)
            ]);
        });
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
                'command' => str_replace($oldSetting, $newSetting, $command->command),
                'usage' => str_replace($oldSetting, $newSetting, $command->usage)
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

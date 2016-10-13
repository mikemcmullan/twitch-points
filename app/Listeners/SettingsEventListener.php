<?php

namespace App\Listeners;
use App\BotCommands\Manager;
use App\Channel;
use App\Command;
use GuzzleHttp\Exception\ClientException;

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
        $this->commandsManager->update($channel, 'currency.get', [
            'command'   => str_replace($oldSetting, $newSetting, config('commands.system.currency.get.command')),
            'usage'     => str_replace($oldSetting, $newSetting, config('commands.system.currency.get.usage'))
        ]);
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
        $this->commandsManager->update($channel, 'giveaway.enter', [
            'command'   => str_replace($oldSetting, $newSetting, config('commands.system.giveaway.enter.command')),
            'usage'     => str_replace($oldSetting, $newSetting, config('commands.system.giveaway.enter.usage'))
        ]);
    }

    /**
     *
     *
     * @param  Channel $channel
     * @param  string  $oldSetting
     * @param  string  $newSetting
     *
     * @return void
     */
    public function followersAlertUpdated(Channel $channel, $oldSetting, $newSetting)
    {
        if (config('twitch.follower_notifications.enabled') !== true) {
            return;
        }

        if ($newSetting === $oldSetting) {
            return;
        }

        $client = new \GuzzleHttp\Client();

        if ($newSetting === true) {
            $method = 'POST';
        } else {
            $method = 'DELETE';
        }

        try {
            $response = $client->request($method, rtrim(config('twitch.follower_notifications.url'), '/') . '/channels', [
                'auth'              => [config('twitch.follower_notifications.username'), config('twitch.follower_notifications.password')],
                'json'              => ['channel' => $channel->name],
                'connect_timeout'   => 2,
                'timeout'           => 2
            ]);
        } catch (ClientException $e) {
            \Log::error('Unable to send request to follower notifications server. ' . $e->getMessage());
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
       $events->listen('settings.updated.followers.alert', 'App\Listeners\SettingsEventListener@followersAlertUpdated');
   }
}

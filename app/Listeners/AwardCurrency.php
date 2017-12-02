<?php

namespace App\Listeners;

use App\Events\NewFollower;
use App\Events\NewSubscription;
use App\Events\ReSubscription;
use App\Currency\Manager;

class AwardCurrency
{
	protected $currencyManager;

	public function __construct(Manager $currencyManager)
	{
		$this->currencyManager = $currencyManager;
	}

	/**
	 * Handle the event.
	 *
	 * @param  NewFollower  $event
	 * @return void
	 */
	public function handle($event)
	{
		$amount = 0;

		$users = collect();

		if ($event instanceof NewFollower) {
			$amount = $event->channel->getSetting('followers.new-awarded-currency-amount', 0);
			$users = $event->followers;
		}

		if ($event instanceof NewSubscription || $event instanceof ReSubscription) {
			$amount = $event->channel->getSetting('subscribers.new-awarded-currency-amount', 0);
			$users = collect([$event->subscriber]);
		}

		if ($amount == 0) {
			return;
		}

		$users->each(function ($user) use ($event, $amount) {
			$this->currencyManager->add($event->channel, $user['id'], $amount);
		});
	}
}

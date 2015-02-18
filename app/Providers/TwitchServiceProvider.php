<?php namespace App\Providers;

use App\Services\TwitchSDKAdapter;
use Illuminate\Support\ServiceProvider;
use ritero\SDK\TwitchTV\TwitchSDK;

class TwitchServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('ritero\SDK\TwitchTV\TwitchSDK', function($app)
		{
			$config = [
				'client_id'      => $app['config']->get('twitch.credentials.client_id'),
				'client_secret'  => $app['config']->get('twitch.credentials.client_secret'),
				'redirect_uri'   => $app['config']->get('twitch.credentials.redirect_uri')
			];

			return new TwitchSDK($config);
		});
	}

}

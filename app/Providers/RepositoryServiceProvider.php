<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

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
		$this->app->bind(
			'App\Repositories\ChatUsers\ChatUserRepository',
			'App\Repositories\ChatUsers\MySqlChatUserRepository'
		);

		$this->app->bind(
			'App\Repositories\Users\UserRepository',
			'App\Repositories\Users\EloquentUserRepository'
		);

		$this->app->bind(
			'App\Repositories\TrackPointsSessions\TrackPointsSession',
			'App\Repositories\TrackPointsSessions\EloquentTrackPointsSession'
		);

		$this->app['auth']->extend('repo', function($app)
		{
			return $app->make('App\Repositories\Users\UserRepository');
		});
	}

}

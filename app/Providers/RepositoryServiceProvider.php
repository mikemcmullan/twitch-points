<?php namespace App\Providers;

use App\Repositories\Chatters\EloquentChatterRepository;
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
			'App\Repositories\Chatters\ChatterRepository',
			'App\Repositories\Chatters\MySqlChatterRepository'
		);

		$this->app->bind('App\Repositories\Chatters\EloquentChatterRepository', function($app)
		{
			$repo = new EloquentChatterRepository($app['App\Chatter'], $app['db'], $app['config']);

			foreach($app['config']->get('twitch.points.hidden_chatters', []) as $handle)
			{
				$repo->getHiddenChatters()->push($handle);
			}

			return $repo;
		});

		$this->app->bind(
			'App\Repositories\Users\UserRepository',
			'App\Repositories\Users\EloquentUserRepository'
		);

		$this->app->bind(
			'App\Repositories\TrackPointsSessions\TrackSessionRepository',
			'App\Repositories\TrackPointsSessions\EloquentTrackSessionRepository'
		);
//		$this->app['auth']->extend('repo', function($app)
//		{
//			return $app->make('App\Repositories\Users\UserRepository');
//		});
	}

}

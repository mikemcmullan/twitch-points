<?php namespace App\Providers;

use App\Repositories\Chatter\EloquentChatterRepository;
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
			'App\Contracts\Repositories\ChatterRepository',
			'App\Repositories\Chatter\MySqlChatterRepository'
		);

		$this->app->bind('App\Repositories\Chatter\EloquentChatterRepository', function($app)
		{
			$repo = new EloquentChatterRepository($app['App\Chatter'], $app['db'], $app['config']);

			foreach($app['config']->get('twitch.points.channel_mods', []) as $handle)
			{
				$repo->getHiddenChatters()->push($handle);
			}

			return $repo;
		});

		$this->app->bind(
			'App\Contracts\Repositories\UserRepository',
			'App\Repositories\User\EloquentUserRepository'
		);

		$this->app->bind(
			'App\Contracts\Repositories\TrackSessionRepository',
			'App\Repositories\TrackPointsSession\EloquentTrackSessionRepository'
		);
	}

}

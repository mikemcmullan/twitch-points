<?php namespace App\Http\Middleware;

use Closure;

class ProtectApi {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->server('REMOTE_ADDR') !== $request->server('SERVER_ADDR') && env('APP_ENV') === 'production')
		{
			return response()->json(['error' => 'Unauthorized.'], 401);
		}

		return $next($request);
	}

}

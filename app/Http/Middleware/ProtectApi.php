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
		if ($request->server('HTTP_ACCESS_TOKEN') !== env('API_KEY'))
		{
			return response()->json(['error' => 'Unauthorized.'], 401);
		}

		return $next($request);
	}

}

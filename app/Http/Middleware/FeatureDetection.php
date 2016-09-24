<?php

namespace App\Http\Middleware;

use Closure;

class FeatureDetection
{
    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $feature = null)
    {
        $channel = $request->route()->getParameter('channel');

        if ($feature && ! $channel->hasFeature($feature)) {
            return redirect()
                ->route('home_path', $channel->slug)
                ->with('message', 'You\'re not allowed to use this feature.');
        }

        return $next($request);
    }
}

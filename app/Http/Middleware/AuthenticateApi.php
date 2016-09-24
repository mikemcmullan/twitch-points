<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\AccessDeniedException;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $feature = null)
    {
        $channel = $request->route()->getParameter('channel');

        if (($feature && ! $channel->hasFeature($feature))
            || (\Gate::denies('admin-channel', $channel) && array_get(\JWTAuth::parseToken()->getPayload(), 'super-user', false) === false)) {
            throw new AccessDeniedException('You are not allowed to use this api.');
        }

        return $next($request);
    }
}

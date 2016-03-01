<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest(route('login_path', $request->route()->getParameter('channel')->slug));
            }
        }

        // permission name => url
        $paths = [
            'system-control'=> 'system-control',
            // 'commands'      => 'commands',
            'giveaway'      => 'giveaway',
            'timers'        => 'timers',
            'vips'          => 'vips'
        ];

        foreach ($paths as $permission => $path) {
            if ($request->is($path) && ! $this->auth->user()->hasPermission($permission)) {
                return redirect()->route('home_path', $request->route()->getParameter('channel')->slug)->with('message', 'You\'re not allowed to use this feature.');
            }
        }

        return $next($request);
    }
}

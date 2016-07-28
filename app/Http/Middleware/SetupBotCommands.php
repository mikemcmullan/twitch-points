<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\SetupBotCommands as SetupBotCommandsSupport;

class SetupBotCommands
{
    protected $commands;

    public function __construct(SetupBotCommandsSupport $commands)
    {
        $this->commands = $commands;
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
        $this->commands->setup($request->route()->getParameter('channel'));

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\TwitchApi;

use Closure;

class ResolveTwitchUsername
{
    protected $twitchApi;

    public function __construct(TwitchApi $twitchApi)
    {
        $this->twitchApi = $twitchApi;
    }

    protected function getFromCache($username)
    {
        return getUserFromRedis($username);
    }

    protected function addToCache($user)
    {
        addUserToRedis([
            'id'            => $user['id'] ?? $user['twitch_id'],
            'username'      => $user['username'],
            'display_name'  => $user['display_name']
        ]);
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
        if ($username = strtolower($request->get('username'))) {
            $usernames = collect([$username]);
            $final = collect();


            if ($source = $request->get('source')) {
                $usernames->push($source);
            }

            foreach ($usernames as $u) {
                if ($cache = $this->getFromCache($u)) {
                    $final->put($u, $cache);
                    $usernames->pop();
                }
            }

            if (! $usernames->isEmpty()) {

                collect($this->twitchApi->getUsersByUsername($usernames->toArray()))->each(function ($user) use ($final) {
                    $user = [
                        'twitch_id'     => $user['id'],
                        'username'      => $user['login'],
                        'display_name'  => $user['display_name']
                    ];

                    $final->put($user['username'], $user);
                    $this->addToCache($user);
                });

            }

            $newRequest = collect();

            if ($username = $final->get($username)) {
                $newRequest->put('username', array_only($username, ['twitch_id', 'username', 'display_name']));
            } else {
                $newRequest->put('username', null);
            }


            if ($source = $final->get($source)) {
                $newRequest->put('source', array_only($source, ['twitch_id', 'username', 'display_name']));
            } else {
                $newRequest->put('source', null);
            }

            $request->merge($newRequest->toArray());
        }

        return $next($request);
    }
}

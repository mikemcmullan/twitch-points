<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! isApi($request->getHost())) {
            return $next($request);
        }

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Content-Type, Access-Token, Authorization, Origin'
        ];

        if ($request->getMethod() == "OPTIONS") {
            return response('OK', 200, $headers);
        }

        $response = $next($request);

        foreach ($headers as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}

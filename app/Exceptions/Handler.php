<?php

namespace App\Exceptions;

use Exception;
use fXmlRpc\Exception\HttpException as RrcHttpException;
use fXmlRpc\Exception\TransportException;
use App\Exceptions\FileInaccessibleException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
     protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->is('api/bot/*') && ($e instanceof TransportException || $e instanceof RrcHttpException)) {
            return response()->json([
                'error' => 'Unable to connect to Supervisor.',
                'level' => 'regular'
            ]);
        }

        if ($request->is('api/bot/*') && $e instanceof FileInaccessibleException) {
            return response()->json([
                'error' => $e->getMessage(),
                'level' => 'regular'
            ]);
        }

        if ($request->is('api/bot/*') && $e instanceof BotStateException) {
            return response()->json([
                'error' => $e->getMessage(),
                'level' => 'regular'
            ]);
        }

        return parent::render($request, $e);
    }
}

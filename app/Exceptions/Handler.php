<?php

namespace App\Exceptions;

use Exception;
use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TransportException;
use App\Exceptions\FileInaccessibleException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
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
        if ($request->is('api/bot/*') && ($e instanceof TransportException || $e instanceof HttpException)) {
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

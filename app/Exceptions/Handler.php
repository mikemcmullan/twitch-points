<?php

namespace App\Exceptions;

use Exception;
use fXmlRpc\Exception\HttpException as RrcHttpException;
use fXmlRpc\Exception\TransportException;
use App\Exceptions\FileInaccessibleException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Validation\ValidationException;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Exceptions\GiveAwayException;
use App\Exceptions\UnknownHandleException;
use App\Exceptions\InvalidChannelException;
use App\Exceptions\InvalidContentTypeException;

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
        InvalidChannelException::class
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
        if (isApi($request->getHost())) {

            if ($e instanceof InvalidChannelException ||
                $e instanceof NotFoundHttpException ||
                $e instanceof MethodNotAllowedHttpException ||
                $e instanceof ModelNotFoundException ||
                $e instanceof UnknownHandleException
                ) {
                return response()->json([
                    'error' => 'Not Found',
                    'status'=> 404,
                    'message' => $e->getMessage()
                ], 404);
            }

            if ($e instanceof UnauthorizedHttpException) {
                return response()->json([
                    'error'   => 'Unauthorized Request',
                    'code'    => 401,
                    'message' => $e->getMessage()
                ], 401);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error'   => 'Bad Request',
                    'code'    => 400,
                    'message' => [
                        'validation_errors' => $e->errors()
                    ]
                ], 400);
            }

            if ($e instanceof \InvalidArgumentException) {
                return response()->json([
                    'error'   => 'Bad Request',
                    'code'    => 400,
                    'message' => $e->getMessage()
                ], 400);
            }

            if ($e instanceof GiveAwayException) {
                return response()->json([
                    'error' => 'Conflict',
                    'code'  => 409,
                    'message' => $e->getMessage()
                ], 409);
            }
        }

        // if ($request->is('api/bot/*') && ($e instanceof TransportException || $e instanceof RrcHttpException)) {
        //     return response()->json([
        //         'error' => 'Unable to connect to Supervisor.',
        //         'level' => 'regular'
        //     ]);
        // }
        //
        // if ($request->is('api/bot/*') && $e instanceof FileInaccessibleException) {
        //     return response()->json([
        //         'error' => $e->getMessage(),
        //         'level' => 'regular'
        //     ]);
        // }
        //
        // if ($request->is('api/bot/*') && $e instanceof BotStateException) {
        //     return response()->json([
        //         'error' => $e->getMessage(),
        //         'level' => 'regular'
        //     ]);
        // }

        return parent::render($request, $e);
    }
}

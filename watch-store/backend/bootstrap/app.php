<?php

use App\Exceptions\InvalidOrderStatusTransitionException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Consistent {success, message, code} JSON body for every API error,
        // with stack traces / gateway payloads / internal details never
        // reaching the client — regardless of debug mode.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            [$status, $code, $message] = match (true) {
                $e instanceof ValidationException => [422, 'VALIDATION_FAILED', 'Validation failed'],
                $e instanceof AuthenticationException => [401, 'UNAUTHENTICATED', 'Unauthenticated'],
                $e instanceof ModelNotFoundException => [404, 'NOT_FOUND', 'Resource not found'],
                $e instanceof InvalidOrderStatusTransitionException => [409, 'INVALID_STATUS_TRANSITION', $e->getMessage()],
                $e instanceof HttpExceptionInterface => [$e->getStatusCode(), 'HTTP_ERROR', $e->getMessage() ?: 'Request failed'],
                default => [500, 'SERVER_ERROR', 'Something went wrong. Please try again later.'],
            };

            $body = ['success' => false, 'message' => $message, 'code' => $code];

            if ($e instanceof ValidationException) {
                $body['errors'] = $e->errors();
            }

            return response()->json($body, $status);
        });
    })->create();

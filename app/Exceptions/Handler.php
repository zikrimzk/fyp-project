<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Services\ExceptionReference;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            app(ExceptionReference::class)->report($e);
            return false;
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof HttpExceptionInterface || $e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            $reference = $request->attributes->get('request_id');
            $message = "Something went wrong. Please try again. If the problem continues, contact the system administrator and provide reference ID {$reference}.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'reference_id' => $reference], 500);
            }

            return response()->view('errors.500', ['message' => $message, 'reference' => $reference], 500);
        });
    }
}

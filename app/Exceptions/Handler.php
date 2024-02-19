<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

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
        /*$this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ], 404);
            }
        });*/

        $this->renderable(function (Throwable $e, Request $request) {
            if ($e instanceof ValidationException) {
                return $this->handleValidationException($e, $request);
            }

            if ($request->is('api/*')) {
                return $this->handleOtherExceptions($e);
            }
        });
    }

    protected function handleValidationException(ValidationException $e, Request $request): JsonResponse
    {
        $errors = [];

        foreach ($e->errors() as $field => $errorMessages) {
            $errors[$field] = $errorMessages[0];
        }

        return response()->json([
            'errors' => $errors,
        ], 400);
    }

    protected function handleOtherExceptions(Throwable $e): JsonResponse
    {
        return response()->json([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ], 404);
    }
}

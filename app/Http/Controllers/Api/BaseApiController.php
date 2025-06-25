<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BaseApiController extends Controller
{
    use ApiResponse;

    /**
     * Handle exceptions and return appropriate responses.
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        return $this->serverErrorResponse(
            'An unexpected error occurred.',
            config('app.debug') ? $e->getMessage() : null
        );
    }

    /**
     * Execute a callable and handle exceptions.
     */
    protected function executeWithExceptionHandling(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
} 
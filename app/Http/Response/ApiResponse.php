<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Build a successful JSON response.
     *
     * @param  mixed  $data  Payload (Resource, array, or scalar)
     */
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Build a failed JSON response.
     *
     * @param  mixed  $errors  Optional validation or domain error details
     */
    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Build a 201 Created JSON response.
     */
    public static function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Build a 404 Not Found JSON response.
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Build a 403 Forbidden JSON response.
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Build a 401 Unauthorized JSON response.
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Build a 422 Validation Error JSON response.
     *
     * @param  mixed  $errors  Validation error bag
     */
    public static function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }
}

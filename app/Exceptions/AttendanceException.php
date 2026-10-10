<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceException extends Exception
{
    /**
     * @param  string  $message  Localized error message
     * @param  string  $messageCode  Stable message code (MSG-xx)
     * @param  int  $status  HTTP status code
     */
    public function __construct(
        string $message,
        private readonly string $messageCode,
        private readonly int $status = 422,
    ) {
        parent::__construct($message);
    }

    /**
     * Build an exception for an expired or missing QR token (MSG-01).
     */
    public static function qrExpired(): self
    {
        return new self(__('messages.MSG-01'), 'MSG-01');
    }

    /**
     * Build an exception when the student is not enrolled (MSG-02).
     */
    public static function notEnrolled(): self
    {
        return new self(__('messages.MSG-02'), 'MSG-02');
    }

    /**
     * Build an exception when the student already checked in (MSG-03).
     */
    public static function alreadyCheckedIn(): self
    {
        return new self(__('messages.MSG-03'), 'MSG-03');
    }

    /**
     * Build an exception when the session is not open (MSG-04).
     */
    public static function sessionNotOpen(): self
    {
        return new self(__('messages.MSG-04'), 'MSG-04');
    }

    /**
     * Render the exception as an ApiResponse-compatible JSON body for API clients.
     */
    public function render(Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => null,
            'code' => $this->messageCode,
        ], $this->status);
    }
}

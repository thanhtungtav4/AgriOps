<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => ['trace_id' => request()->header('X-Trace-ID') ?? uniqid()],
        ], $status);
    }

    protected function error(
        string $code,
        string $message,
        array $details = [],
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'details' => $details ?: null,
                'trace_id' => request()->header('X-Trace-ID') ?? uniqid(),
            ]),
        ], $status);
    }

    protected function authError(string $code, string $message, array $details = []): JsonResponse
    {
        return $this->error($code, $message, $details, 401);
    }

    protected function forbiddenError(string $message, array $details = []): JsonResponse
    {
        return $this->error('AUTH_FORBIDDEN', $message, $details, 403);
    }

    protected function notFoundError(string $message): JsonResponse
    {
        return $this->error('RESOURCE_NOT_FOUND', $message, [], 404);
    }

    protected function validationError(string $field, string $message): JsonResponse
    {
        return $this->error(
            'VALIDATION_REQUIRED',
            $message,
            ['field' => $field, 'rule' => 'required'],
            422
        );
    }

    protected function domainError(string $message, array $details = [], string $code = 'DOMAIN_ERROR'): JsonResponse
    {
        return $this->error($code, $message, $details, 422);
    }
}

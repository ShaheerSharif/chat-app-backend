<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait SendsJSONResponse
{
    protected function jsonSuccess(
        string $message = 'Success',
        $data = null,
        int $status = 200,
        $meta = null
    ): JsonResponse {
        return response()
            ->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
                'meta' => array_merge([
                    'retrieved_at' => now()->toDateTimeString(),
                    'status' => $status,
                ], is_array($meta) ? $meta : []),
            ], $status)
            ->header('Content-Type', 'application/json')
            ->header('Accept', 'application/json');
    }

    protected function jsonError(
        string $message = 'Error',
        int $status = 400,
        $meta = null
    ): JsonResponse {
        return response()
            ->json([
                'success' => false,
                'message' => $message,
                'meta' => array_merge([
                    'retrieved_at' => now()->toDateTimeString(),
                    'status' => $status,
                ], is_array($meta) ? $meta : []),
            ], $status)
            ->header('Content-Type', 'application/json')
            ->header('Accept', 'application/json');
    }
}

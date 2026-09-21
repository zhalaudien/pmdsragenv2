<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Standard success JSON response
     */
    protected function successResponse(mixed $data = null, string $message = 'Data berhasil diproses.', int $status = 200, array $extraMeta = []): JsonResponse
    {
        $meta = array_merge([
            'timestamp' => now()->toIso8601String(),
        ], $extraMeta);

        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ];

        return response()->json($response, $status);
    }

    /**
     * Standard error JSON response
     */
    protected function errorResponse(string $message = 'Terjadi kesalahan.', int $status = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $response['meta'] = [
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json($response, $status);
    }

    /**
     * Helper to get effective cabang_id from request attributes
     */
    protected function getEffectiveCabangId(): int
    {
        return (int)request()->attributes->get('effective_cabang_id');
    }
}

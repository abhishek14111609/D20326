<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Success response method.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $this->getValidHttpCode($code));
    }

    /**
     * Error response method.
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message = 'Error', int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $this->getValidHttpCode($code));
    }

    /**
     * Get a valid HTTP status code
     * 
     * @param int $code
     * @param int $default
     * @return int
     */
    protected function getValidHttpCode($code, $default = 500)
    {
        $validCodes = [
            // 1xx: Informational
            100, 101, 102, 103,
            // 2xx: Success
            200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
            // 3xx: Redirection
            300, 301, 302, 303, 304, 305, 306, 307, 308,
            // 4xx: Client Error
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 421, 422, 423, 424, 425, 426, 428, 429, 431, 451,
            // 5xx: Server Error
            500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511
        ];

        return in_array($code, $validCodes) ? $code : $default;
    }
}

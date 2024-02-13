<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @param $statusCode
     *
     * @return JsonResponse
     */
    public function sendResponse($result, $message, $statusCode = 200)
    {
        $response = [
            'success' => true,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @param $statusCode
     *
     * @return JsonResponse
     */
    public function sendPaginatedResponse($result, $message, $statusCode = 200)
    {
        $result['success'] = true;
        $result['statusCode'] = $statusCode;
        $result['message'] = $message;

        return response()->json($result, $statusCode);
    }

    /**
     * return error response.
     *
     * @param $errorMessage
     * @param  array  $errorMessages
     * @param  int  $code
     * @param   $exception
     *
     * @return JsonResponse
     */
    public function sendError($errorMessage, $errorMessages = [], $statusCode = 400, \Exception $exception = null)
    {
        $response = [
            'success' => false,
            'statusCode' => $statusCode,
            'message' => $errorMessage,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        //log exception object if is set so that it can be tracked
        if (isset($exception)) {
            Log::error($exception);
        }

        return response()->json($response, $statusCode);
    }
}

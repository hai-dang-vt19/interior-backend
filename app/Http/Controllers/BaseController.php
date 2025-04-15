<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function sendRedirectAjax($routeName, $param = null)
    {
        return response()->json([
            'redirect' => route($routeName, $param)
        ], 200);
    }

    protected function sendSuccess($routeName, $data = null, $message = null, $param = null)
    {
        $response = [
            'data' => $data,
            'msg' => $message,
        ];

        return to_route($routeName, $param)->with('dataSuccess', $response);
    }

    protected function sendError($routeName, $statusCode = 500, $message = null, $param = null, $data = [])
    {
        $response = [
            'error' => [
                'code' => (int) $statusCode,
                'data' => $data,
                'msg' => $message,
            ],
        ];

        return to_route($routeName, $param)->with(['dataError' => $response]);
    }

    // Send success api
    public function apiSuccess($data, $message = '', $code = 200) : JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    // Send error api
    public function apiError($error, $errorMessages = [], $code = 404) : JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}

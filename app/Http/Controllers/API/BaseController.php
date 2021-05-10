<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function SendResponse($result, $message){
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    public function SendError($error, $errorMessage = [], $code = 404){
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessage)){
            $response['data']=$errorMessage;
        }
        return response()->json($response, $code);
    }
}

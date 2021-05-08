<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function SendResponse($result, $message){
        $respones = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($respones, 200);
    }

    public function SendError($error, $errorMessage = [], $code = 404){
        $respones = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessage)){
            $responses['data']=$errorMessage;
        }
        return response()->json($respones, $code);
    }
}

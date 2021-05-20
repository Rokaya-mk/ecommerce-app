<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function makeOrder(Request $request){
        $newOrder = new Order();
        $user=User::findorFail(Auth::id());
        try {
            $newOrder->user_id=$user;

        } catch (\Throwable $th) {
            //throw $th;
        }

    }
}

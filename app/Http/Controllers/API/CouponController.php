<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use  Carbon\Carbon;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponController extends BaseController
{
    //display all coupons for admin
    public function displayCoupons()
    {
        try {
            $user=User::find(Auth::id());
        if($user->is_Admin == 1){
            $coupons = Coupon::orderBy('id', 'DESC')->get();
            if($coupons->count()==0){
                return $this->SendError('coupons list is empty');
            }
            else{
                return $this->SendResponse($coupons,'Coupons list retreived Sucessfully !');
            }
        }
        else{
            return $this->SendError('You do not have rights to access to coupons list');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

    }

    //add new coupon
    public function storeNewCoupon(Request $request)
    {
        try {
            //validate data
        $validateData=Validator::make($request->all(), [
            'discount_code'=>'required',
            'discount_type'=>'required|in:Fix,PERCENTAGE',
            'discount_value'=>'required',
            'expired_date'=>'required|date',

        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());
        $user=User::find(Auth::id());
        if($user->is_Admin == 1){
            //store new coupon
            $coupon =new Coupon();
            $coupon->discount_code = $request->discount_code;
            $coupon->discount_type = $request->discount_type;
            $coupon->discount_value = $request->discount_value;
            $coupon->expired_date = Carbon::parse($request->expired_date)->format('Y-m-d H:i:s');
            if($coupon->expired_date<=Carbon::now()){
                return $this->SendError('expired date should not be less than date now');
            }
            $coupon->save();
            return $this->SendResponse($coupon, 'Coupon added Successfully!');
        }else{
            return $this->SendError('You do not have rights to add coupon');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }


    }
    //Update Coupon
    public function updateCoupon(Request $request, $id)
    {
        try {
            //validate data
        $validateData=Validator::make($request->all(), [
            'discount_code'=>'required',
            'discount_type'=>'required|in:Fix,PERCENTAGE',
            'discount_value'=>'required',
            'expired_date'=>'required|date',

        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());
        $user=User::find(Auth::id());
        if($user->is_Admin != 1){
            return $this->SendError('You do not have rights to update coupon');
        }else{
            $coupon = Coupon::where('id',$id)->first();
            if ($coupon) {
                $coupon->discount_code = $request->discount_code;
                $coupon->discount_type = $request->discount_type;
                $coupon->discount_value = $request->discount_value;
                $coupon->expired_date = Carbon::parse($request->expired_date)->format('Y-m-d H:i:s');
                if($coupon->expired_date<=Carbon::now()){
                    return $this->SendError('expired date should not be less than date now');
                }
                $coupon->save();
                return $this->SendResponse($coupon, 'Coupon Updated Successfully!');
            }else{
                return $this->SendError('This coupon not founded');
            }
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //delete coupon
    public function destroyCoupon($id)
    {
        try {
            $user=User::find(Auth::id());
        if($user->is_Admin != 1){
            return $this->SendError('You do not have rights to update coupon');
        }else{
            $coupon = Coupon::where('id', $id)->first();
        if(is_null($coupon))
            return $this->SendError('Coupon not founded');
        if ($coupon) {
            $coupon->delete();
            return $this->SendResponse($coupon,'Coupon Deleted Sucessfully');
            }
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

    }

    //upply coupon
    public function applyCoupon(Request $request){

        $validateData=Validator::make($request->all(), [
            'coupon_code'=>'required',
        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());

        $coupon = Coupon::where(DB::raw("BINARY `discount_code`"),$request->coupon_code)->first();
        if($coupon){
            $getDate=Carbon::now()->format('Y-m-d H:i:s');
            if($coupon->expired_date>=$getDate){
                return $this->SendResponse($coupon,'Coupon applied Sucessfully');
            }else{
                return $this->SendError('This Coupon is expired');
            }
        }else{
            return $this->SendError('Invalid Coupon');
        }
    }


}

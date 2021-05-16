<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use  Carbon\Carbon;
use App\Models\Coupon;
class CouponController extends BaseController
{

    public function displayCoupons()
    {
        $coupons = Coupon::orderBy('id', 'DESC')->get();
        if($coupons->count()==0){
            return $this->SendError('coupons list is empty');
        }
        else{
            return $this->SendResponse($coupons,'list of coupons');
        }
    }


    public function storeNewCoupon(Request $request)
    {
        //validate data
        $validateData=Validator::make($request->all(), [
            'discount_code'=>'required',
            'discount_type'=>'required',
            'discount_value'=>'required',
            'expired_date'=>'required|date',

        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());
        try {
            //store new coupon
            $coupon =new Coupon();

            $coupon->discount_code = $request->discount_code;
            $coupon->discount_type = $request->discount_type;
            $coupon->discount_value = $request->discount_value;
            $coupon->expired_date = Carbon::parse($request->expired_date)->format('Y-m-d H:i:s');
            $coupon->save();
            return $this->SendResponse($coupon, 'Coupon added Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error to store coupon',$th->getMessage());
        }
    }
    //Update Coupon
    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::where('id',$id)->first();
        if ($coupon) {
            $coupon->discount_code = $request->discount_code;
            $coupon->discount_type = $request->discount_type;
            $coupon->discount_value = $request->discount_value;
            $coupon->expired_date = Carbon::parse($request->expired_date)->format('Y-m-d H:i:s');
            try {
                $coupon->save();
                return $this->SendResponse($coupon, 'Coupon Updated Successfully!');
            } catch (\Throwable $th) {
                return $this->SendError('cant\'t Update Coupon',$th->getMessage());
            }
        }
    }

    //delete coupon

    public function destroyCoupon($id)
    {
        $coupon = Coupon::where('id', $id)->first();
        if(is_null($coupon))
            return $this->SendError('Coupon not founded');
        if ($coupon) {
            try {
                $coupon->delete();
            return $this->SendResponse($coupon,'Coupon Deleted Sucessfully');
            } catch (\Throwable $th) {
                return $this->SendError('cant\'t Delete Coupon',$th->getMessage());
            }
        }
    }



}
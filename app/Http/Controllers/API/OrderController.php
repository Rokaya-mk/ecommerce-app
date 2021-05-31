<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\User_bag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{

    //make order
    public function makeOrder(Request $request){
        try {
            $user_id=Auth::user()->id;
            //get products in user_bag for user
            $products_bag=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();
            if($products_bag->isEmpty())
                return $this->SendError('you don\'t have any products to order in your bag,try to add some!');
            //delete products that's not available
            foreach($products_bag as $item){
                $product_item=Product::findOrFail($item->product_id);
                if(!$product_item){
                    return $this->SendError('this product not founded');
                }else{
                    if(($product_item->quantity)<=0){
                        //if it is not available delete item in bag
                        try {
                            $item->delete();
                            return $this->SendResponse($item,'this product is not available now');
                        } catch (\Throwable $th) {
                            return $this->SendError('Error',$th->getMessage());
                        }
                    }
                }
            }
        //create order
        try {
            $order=new Order();
            $lastOrder = Order::orderBy('id', 'desc')->first();
            //if there is no order yet
            if(is_null($lastOrder)){
                $order->id=1;
            }else{
                $order->id=$lastOrder->id +1;
            }
            //dd($order->id);
            $order->user_id=$user_id;

            //generate unique_order_id
            $latestOrder = Order::orderBy('created_at','DESC')->first();
            if(is_null($latestOrder)){
                $order->unique_order_id = '#'.str_pad( 1, 8, "0", STR_PAD_LEFT);
            }else{
                    $order->unique_order_id = '#'.str_pad($latestOrder->id + 1, 8, "0", STR_PAD_LEFT);
                }
            $products_bag=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();
            if($products_bag->isEmpty())
                return $this->SendError('you don\'t have any products to order in your bag,try to add some!');

            //calculate total amount in bag
            $orderTotal=0;
            foreach($products_bag as $item){
                //get product in product table
                $product_item=Product::findOrFail($item->product_id);
                $orderTotal+=($item->item_quantity)*($product_item->price);
                // $product_item->quantity=($product_item->quantity)-($item->item_quantity);
               // $product_item->save();
            }
            //if order has coupon
            if($request->has('coupon')){
                //get coupon discount
                $coupon = Coupon::where('discount_code', $request->coupon)->first();
                if($coupon->discount_type=='PERCENTAGE'){
                    $orderTotal = $orderTotal - (($coupon->discount_value / 100) * $orderTotal);
                }else if($coupon->discount_type=='Fix')
                    $orderTotal = $orderTotal - ($coupon->discount_value) ;

                $order->hasCoupon=true;
                $order->couponDiscount=$coupon->discount_value;
                $order->coupon_id=$coupon->id;
                $order->save();
            }
            if($request->has('payment_method')){
                try {
                    $payment=new Payment();
                $payment->user_id=$user_id;
                $payment->order_id=$order->id;
                $payment->payment_method=$request->payment_method;
                $payment->amount=$orderTotal;
                $payment->payment_date=Carbon::now()->format('Y-m-d H:i:s');
                $payment->save();
                } catch (\Throwable $th) {
                    return $this->SendError('Error',$th->getMessage());
                }
            }else{
                return $this->SendError('you must specify payment method');
            }

            //update quantity for products
            foreach($products_bag as $item){
                $item->is_final_bag="old";
                $product_item=Product::findOrFail($item->product_id);
                //substract quantity taken by user
                $product_item->quantity=($product_item->quantity)-($item->item_quantity);
                $product_item->save();
                $item->save();
            }
            return $this->SendResponse($order,'order created Successfully');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //dispay all orders for admin
    public function allOrders(Request $request){

       $user_id=Auth::user()->id;
       $user=User::findOrFail($user_id);
       if($user->is_Admin==1){
            $count=Order::count();
            if($count!=0){
                $orders=Order::orderBy('id', 'DESC')->get();
                return $this->SendResponse([$orders,$count],'Orders list retrieved successfully');
            }else{
                return $this->SendError('you don\'t have any order');
            }
       }else{
        return $this->SendError('You do not have rights to access');
       }

    }
    //display opened orders to admin
    public function getOpenedOrders(){
        try {
            $user_id=Auth::user()->id;
        $user=User::findOrFail($user_id);
        if($user->is_Admin!=1){
            return $this->SendError('You do not have rights to access');
        }else{
            $orders=Order::where('money_payement',0)->orWhere('is_order_sent',0)->orderBy('id', 'DESC')->get();
            if($orders->isEmpty())
                return $this->sendError('their is any opened order!');
            return $this->SendResponse($orders,'Opened Orders list retrieved successfully');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //display closed orders to admin
    public function getClosedOrders(){
        try {
            $user_id=Auth::user()->id;
            $user=User::findOrFail($user_id);
        if($user->is_Admin!=1){
            return $this->SendError('You do not have rights to access');
        }else{
            $orders=Order::where('money_payement',1)->where('is_order_sent',1)->orderBy('id', 'DESC')->get();
            if($orders->isEmpty())
                return $this->sendError('their is any closed order!');
            return $this->SendResponse($orders,'closed Orders list retrieved successfully');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    //confirm money recieved
    public function ConfirmMoneyRecieve($orderId){
        try {
                $user_id=Auth::user()->id;
                $user=User::findOrFail($user_id);
            if($user->is_Admin!=1){
                return $this->SendError('You do not have rights to access');
            }else{
                $order=Order::findOrFail($orderId);
                if(is_null($order))
                    return $this->SendError('order not founded');
                $order->money_payement=1;
                $order->save();
                return $this->SendResponse($order,'you confirme received money of this order successfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    //confirm delivery
    public function ConfirmDelivery($orderId){
        try {
                $user_id=Auth::user()->id;
                $user=User::findOrFail($user_id);
            if($user->is_Admin!=1){
                return $this->SendError('You do not have rights to access');
            }else{
                $order=Order::findOrFail($orderId);
                if(is_null($order))
                    return $this->SendError('order not founded');
                if($order->money_payement==0)
                    return $this->SendError('this order hasn\'t been paied yet,you can not confirm delivery!');
                $order->is_order_sent=1;
                $order->save();
                return $this->SendResponse($order,'you have confirme delivery of this order successfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //show user orders list
    public function myOrders(){
        $user_id=Auth::user()->id;
        try {
            $orders=Order::where('user_id',$user_id)->orderBy('id','DESC')->get();
            if($orders->isEmpty())
                return $this->sendError('your order list is empty!');
            return $this->SendResponse($orders,'Your Orders list retrieved successfully');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    //update date sent,date target by admin
    public function updateOrderDate(Request $request,$orderId){
        try {
            $validator=Validator::make($request->all(),[
                'date_sent'   =>'required|date',
                'date_target' =>'required|date'
            ]);
            if ($validator->fails())
                return $this->SendError('Please validate error' ,$validator->errors());
            $user_id=Auth::user()->id;
            $user=User::findOrFail($user_id);
            if($user->is_Admin!=1){
                return $this->SendError('You do not have rights to access');
            }else{
                $order=Order::findOrFail($orderId);
                if(is_null($order))
                    return $this->SendError('order not founded!');
                $date_sent=Carbon::parse($request->date_sent)->format('Y-m-d');
                $date_target=Carbon::parse($request->date_target)->format('Y-m-d');
                if($date_target<$date_sent)
                return $this->SendError('target date must be grater then date sent!');
                try {
                    $order->date_sent=$date_sent;
                    $order->date_target=$date_target;
                    $order->save();
                    return $this->SendResponse($order,'order dates updated successfully');
                } catch (\Throwable $th) {
                    return $this->SendError('Error',$th->getMessage());
                }
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

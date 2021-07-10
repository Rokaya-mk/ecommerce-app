<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Product_size;
use App\Models\Product_size_color_quantity;
use App\Models\User;
use App\Models\User_bag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{

    //make order
    public function makeOrder(Request $request){
        try {
            $user=Auth::user();
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                 //get products in user_bag for user
            $products_bag=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();
            //dd($products_bag);
            if(is_null($products_bag))
                return $this->SendError('you don\'t have any products to order in your bag,try to add some!');
            //delete products that's not available
            $itemsDeleted=[];
            $itemsUpdated=[];
            foreach($products_bag as $item){

                 $product_item=Product::onlyTrashed()->find($item->product_id);


                if(!is_null(( $product_item))){
                    $item->delete();
                    array_push($itemsDeleted,$item);
                    //dd($itemsDeleted);
                }
                    //get size from Product_size table
                    $size=Product_size::where('product_id',$item->product_id)->where('size',$item->size)->first();
                    $productQuantityItem=Product_size_color_quantity::where('product_id',$item->product_id)
                                                                      ->where('size_id',$size->id)
                                                                      ->where('color',$item->color)->first();
                    //$quantityItem=Product_size_color_quantity::find($productQuantityItem->id);
                    //dd($productQuantityItem);

                    //if product  quantity is null
                    if(($productQuantityItem->quantity) == 0){
                        //push item into $itemsDeleted
                            $item->delete();
                            array_push($itemsDeleted,$item);
                    }else if($productQuantityItem->quantity < $item->item_quantity){
                            $item->item_quantity=$productQuantityItem->quantity;
                            $item->save();
                            array_push($itemsUpdated,$item);
                    }
                }

                if((!empty($itemsDeleted)) || (!empty($itemsUpdated))){
                    return $this->SendError([
                        'updatedItems' => $itemsUpdated,
                        'deletedItems' => $itemsDeleted
                    ],'Products Quantities was Changed by available quantities');
                }

        //create order
            $order=new Order();
            $lastOrder = Order::orderBy('id', 'desc')->first();
            //if there is no order yet
            if(is_null($lastOrder)){
                $order->id=1;
            }else{
                $order->id=$lastOrder->id +1;
            }
            //dd($order->id);
            $order->user_id=$user->id;
            //generate unique_order_id
            $products_bag=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();
            //calculate total amount in bag
            $orderTotal=0;
            foreach($products_bag as $item){
                //get product in product table
                $product_item=Product::find($item->product_id);
                //verify if product is offered
                $productOffered=Offer::where('product_id',$item->product_id)->first();
                if(!is_null($productOffered)){
                    $orderTotal+=($item->item_quantity)*($productOffered->offer_product_price);
                }else{
                    $orderTotal+=($item->item_quantity)*($product_item->price);
                }
                //$orderTotal+=($item->item_quantity)*($product_item->price);
            }
            //if order has coupon

            if($request->has('coupon_code')){
                //get coupon discount
                $coupon = Coupon::where(DB::raw('discount_code'),$request->coupon_code)->first();
                //dd($coupon);
                if($coupon->discount_type=='PERCENTAGE'){
                    $orderTotal = $orderTotal - (($coupon->discount_value / 100) * $orderTotal);
                }else if($coupon->discount_type=='Fix'){
                    $orderTotal = $orderTotal - ($coupon->discount_value) ;
                }
                $order->hasCoupon=true;
                $order->couponDiscount=$coupon->discount_value;
                $order->coupon_id=$coupon->id;
            }

            $order->save();
            // if($request->has('payment_method')){

            //     $payment=new Payment();
            //     $payment->user_id=$user->id;
            //     $payment->order_id=$order->id;
            //     $payment->payment_method=$request->payment_method;
            //     $payment->amount_paid=$orderTotal;
            //     $payment->save();
            // }
            // else{
            //     return $this->SendError('you must specify payment method');
            // }
            //update quantity for products
            $productsOrdered=[];
            foreach($products_bag as $item){
                $item->is_final_bag="old";
                $product_item=Product::find($item->product_id);

                //store order id
                $item->order_id=$order->id;
                //store price of product (witout offer)
                $item->product_price=$product_item->price;
                //verify if product has offer
                $productOffered=Offer::where('product_id',$item->product_id)->first();
                if(!is_null($productOffered)){
                    $item->price_after_offer=$productOffered->offer_product_price;
                    $item->has_offer=1;
                }

                $size=Product_size::where('product_id',$product_item->id)->where('size',$item->size)->first();
                $productQuantityItem=Product_size_color_quantity::where('product_id',$product_item->id)
                                            ->where('size_id',$size->id)
                                            ->where('color',$item->color)->first();

                //substract quantity taken by user
                $productQuantityItem->quantity=($productQuantityItem->quantity)-($item->item_quantity);
                $productQuantityItem->save();
                $item->save();
                array_push($productsOrdered,$item);
            }
            return $this->SendResponse([
                                        'YourOrder'=>$order,
                                        'TotalPriceOrder'=>$orderTotal,
                                        'ProductsOrder'=>$productsOrdered,

                                    ],
                                        'order created Successfully');
            }
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

    }

    //dispay all orders for admin
    public function allOrders(Request $request)
    {
        try {
            $user_id=Auth::user()->id;
            $user=User::find($user_id);
            if($user->is_Admin==1){
                 $count=Order::count();
                 if($count!=0){
                     $orders=Order::orderBy('id', 'DESC')->get();
                     return $this->SendResponse([
                                                 'orders'=>      $orders,
                                                 'countOrders'=> $count],
                                                 'Orders list retrieved successfully');
                 }else{
                     return $this->SendError('you don\'t have any order');
                 }
            }else{
             return $this->SendError('You do not have rights to access');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }


    }
    //display opened orders to admin
    public function getOpenedOrders(){
        try {
            $user_id=Auth::user()->id;
        $user=User::find($user_id);
        if($user->is_Admin!=1){
            return $this->SendError('You do not have rights to access');
        }else{
            $orders=Order::where('money_payement',0)->orWhere('is_order_sent',0)->orderBy('id', 'DESC')->get();
            if($orders->isEmpty())
                return $this->sendError('there is any opened order!');
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
            $user=User::find($user_id);
        if($user->is_Admin!=1){
            return $this->SendError('You do not have rights to access');
        }else{
            $orders=Order::where('money_payement',1)->where('is_order_sent',1)->orderBy('id', 'DESC')->get();
            if($orders->isEmpty())
                return $this->sendError('there is any closed order!');
            return $this->SendResponse($orders,'closed Orders list retrieved successfully');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    public function confirmSend(Request $request,$orderId){
        try {
            $validator=Validator::make($request->all(),[
                'date_sent'              => 'required|date',
                'date_target'            => 'required|date',
                'confirm_money_recieved' => 'required|in:0,1',
                'confirm_delivery'       => 'required|in:0,1'
            ]);
            if ($validator->fails())
                return $this->SendError('Please validate error' ,$validator->errors());
            $user_id=Auth::user()->id;
            $user=User::find($user_id);
            if($user->is_Admin!=1){
                return $this->SendError('You do not have rights to access');
            }
            $order=Order::find($orderId);
            if(is_null($order))
                return $this->SendError('order not founded');
            $date_sent=Carbon::parse($request->date_sent)->format('Y-m-d');
            $date_target=Carbon::parse($request->date_target)->format('Y-m-d');
            if($date_target<$date_sent)
                return $this->SendError('target date must be grater then date sent!');

            $order->date_sent=$date_sent;
            $order->date_target=$date_target;
            $order->money_payement=$request->confirm_money_recieved;
            $order->is_order_sent=$request->confirm_delivery;
            $order->save();
            return $this->SendResponse($order,'Order Updated Successfully');

        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //show user orders list
    public function myOrders(){
        try {
            $user=Auth::user();
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                $orders=Order::where('user_id',$user->id)->orderBy('id','DESC')->get();
                if($orders->isEmpty())
                    return $this->sendError('your order list is empty!');

                $deliverdOrders=Order::where('user_id',$user->id)
                                      ->where('money_payement',1)
                                      ->where('is_order_sent',1)
                                      ->orderBy('id','DESC')->get();

                $onProcessingOrders=Order::where('user_id',$user->id)
                                           ->orWhere('date_sent',null)
                                           ->orWhere('date_target',null)
                                           ->orWhere('is_order_sent',0)
                                           ->orderBy('id','DESC')->get();

                 return $this->SendResponse([
                                            'allOrders'          =>$orders,
                                            'delivredOrders'     =>$deliverdOrders,
                                            'onProcessingOrders' =>$onProcessingOrders
                                            ],
                                            'Your Orders list retrieved successfully'
                                        );
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

}

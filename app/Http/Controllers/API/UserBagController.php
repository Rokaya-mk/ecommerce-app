<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Product_image;
use App\Models\Product_size;
use App\Models\Product_size_color_quantity;
use App\Models\User;
use App\Models\User_bag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserBagController extends BaseController
{
    //display bag content
    public function myBag()
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                $items=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();
                if($items->isEmpty())
                    return $this->sendError('your bag is empty');
                //return items with products
                return $this->SendResponse(['itemsBag' => $items,],'bag list retreived Sucessfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    //calculate total price
    public function getTotalBagPrice(Request $request){
        try {
            $user=User::find(Auth::id());
            //dd($user);
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                $validateData=Validator::make($request->all(), [
                    'coupon_code'=> 'nullable|string'
            ]);
            if ($validateData->fails())
                    return $this->SendError(' Invalid data' ,$validateData->errors());

                $userBag=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();
                //dd($userBag);

                if($userBag->isEmpty())
                    return $this->sendError('your bag is empty');
                 //calculate total amount in bag
                $totalPrice=0;
                foreach($userBag as $item){
                    $product_item=Product::find($item->product_id);
                    //verify if product is offered
                    $productOffered=Offer::where('product_id',$item->product_id)->first();
                   // dd($productOffered->offer_product_price);
                    if(!is_null($productOffered)){
                        $totalPrice+=($item->item_quantity)*($productOffered->offer_product_price);
                    }else{
                        $totalPrice+=($item->item_quantity)*($product_item->price);
                    }

                }

                //if order has coupon
                if($request->has('coupon_code')){
                    //get coupon discount
                    $coupon = Coupon::where('discount_code', $request->coupon_code)->first();
                    //dd($coupon);
                    if($coupon){
                        if($coupon->discount_type=='PERCENTAGE'){
                            $totalPrice = $totalPrice - (($coupon->discount_value / 100) * $totalPrice);
                        }else if($coupon->discount_type=='Fix'){
                            $totalPrice = $totalPrice - ($coupon->discount_value) ;
                        }
                    }

                }
                return $this->SendResponse(['totalPrice'=>$totalPrice],'total Price calculated Sucessfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //add product to my bag
    public function addTobag(Request $request,$id){
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                    //validate data
            $validateData=Validator::make($request->all(), [
                'item_quantity'=>'required',
                'color'=>'required',
                'size'=>'required|in:S,M,L,XL,XXL',
            ]);
            if ($validateData->fails())
                    return $this->SendError(' Invalid data' ,$validateData->errors());
            $product=Product::onlyTrashed()->find($id);

            //dd(($product);
            if(!(is_null($product))){
                return $this->SendError('Product not available');
            }
            //search all products not ordered in user_bag
            $cart=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();

            foreach($cart as $item){
                if(($item->product_id == $id) && ($item->color == $request->color) && ($item->size == $request->size))
                return $this->SendError('Product already exist in your bag');
            }
            //verify that size exist in database
            $size=Product_size::where('product_id',$id)->where('size',$request->size)->first();
            if(is_null($size)){
                return $this->SendError('the Product not available in '.$request->size. ' size');
            }
            //verify color
            $productColor=Product_size_color_quantity::where('product_id',$id)
                                                ->where('size_id',$size->id)
                                                ->where('color',$request->color)
                                                ->first();

            if(is_null($productColor)){
                return $this->SendError('the Product not available in '.$request->color. ' color');
            }
            //if product quantity less than item quantity update quantity ==> make $quantityUpdated=true
            $quantityUpdated=false;
            //verify quantity
            if($productColor->quantity==0){
                return $this->SendError('This Product not available');
            }
            $newItem = new User_bag();
            $newItem->user_id=$user->id;
            $newItem->product_id=$id;
            //update quantity
            if( $productColor->quantity < $request->item_quantity){
                $newItem->item_quantity=$productColor->quantity;
                $quantityUpdated=true;
            }else{
                $newItem->item_quantity=$request->item_quantity;
            }
            $newItem->color=$request->color;
            $newItem->size=$request->size;
            $newItem->is_final_bag='new';
            $newItem->save();
            if($quantityUpdated==true){
                return $this->SendResponse($newItem,'Product added to the bag ,with modification on product quantity by available quantity');
            }else{
                return $this->SendResponse($newItem,'Product added to Bag Sucessfully');
                }
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    //update product
    public function updateBag(Request $request,$id)
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                    //validate data
                $validateData=Validator::make($request->all(), [
                    'item_quantity'=>'required',
                    'color'=>'required',
                    'size'=>'required|in:S,M,L,XL,XXL',
            ]);
            if ($validateData->fails())
                    return $this->SendError(' Invalid data' ,$validateData->errors());

            $cartItem=User_bag::where('product_id',$id)->where('is_final_bag','new')->first();
            //dd($cartItem);
            if(is_null($cartItem)){
                return $this->SendError('Product not found');
            }

            //verify that size exist in database
            $size=Product_size::where('product_id',$id)->where('size',$request->size)->first();
            if(is_null($size)){
                return $this->SendError('the Product not available in '.$request->size. ' size');
            }
            //verify color
            $productColor=Product_size_color_quantity::where('product_id',$id)
                                                    ->where('size_id',$size->id)
                                                    ->where('color',$request->color)
                                                    ->first();
            //dd($productColor);
            if(is_null($productColor)){
                return $this->SendError('the Product not available in '.$request->color. ' color');
            }

            //if product quantity less than item quantity update quantity ==> make $quantityUpdated=true
            $quantityUpdated=false;


            //verify quantity
            if($productColor->quantity==0){
                return $this->SendError('This Product not available');
            }
              //update quantity
            if( $productColor->quantity < $request->item_quantity){
                $cartItem->item_quantity=$productColor->quantity;
                $quantityUpdated=true;
            }else{
                $cartItem->item_quantity=$request->item_quantity;
            }
            $cartItem->color=$request->color;
            $cartItem->size=$request->size;
            $cartItem->save();
            if($quantityUpdated==true){
                return $this->SendResponse($cartItem,'product bag Updated ,with modification on product quantity by available quantity');
            }else{
                return $this->SendResponse($cartItem,'product bag Updated Sucessfully');
                }
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //delete product in the bag
    public function deleteProductBag($id)
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                $cartItem=User_bag::where('user_id',$user->id)
                                    ->where('product_id',$id)
                                    ->where('is_final_bag','new');
        if(is_null($cartItem))
            return $this->sendError('product not founded in your bag');
            $cartItem->delete();
        return $this->SendResponse($cartItem, 'product deleted successfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error to delete product',$th->getMessage());
        }
    }
    public function destroyBag()
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin!=0){
                return $this->sendError('You do not have rights to access ');
            }else{
                $cart=User_bag::where('user_id',$user->id)->where('is_final_bag','new')->get();
                foreach($cart as $item){
                    $item->delete();
                }
            return $this->SendResponse($cart,'bag Deleted Sucessfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }


}

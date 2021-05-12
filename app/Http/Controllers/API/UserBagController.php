<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User_bag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserBagController extends BaseController
{
    //display bag content
    public function myBag(Request $request)
    {
        if(auth('api')->user()){
            $user_id=auth('api')->user()->id;
            $cart=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();
            if($cart->isEmpty())
                return $this->sendError('empty');
            //$products=$cart->products()->get();
            $products=[];
            foreach($cart as $item){
                $products[]=[
                    'id'=>$item->product_id,
                ];
            }
            $products_info=DB::table('products')->whereIn('id',$products)->get();
            //return cart with products
            return $this->SendResponse([$cart,$products_info],'bag list');
        }
        else if(Auth::guest()){
            $cart = Session::get('cart');
            if(!$cart)
                return $this->sendError('your bag is empty');
            return $this->sendResponse($cart,'Products list');
        }

    }

    public function addTobag(Request $request,$id){

        $product=Product::findOrFail($id);
        //dd($product);
        if(!$product){
            return $this->SendError('Product not found');
        }
        //if user is guest
        if(!(auth('api')->user())){
            $cart = session()->get('cart');
            if(!$cart){
                $cart=[
                    $id=>[
                        'product_id'=>$product->id,
                        'item_quantity'=>$request->item_quantity,
                        'color'=> $request->color,
                        'size' =>$request->size,
                        'is_final_bag' =>'new'
                    ]
                ];
                session()->put('cart', $cart);
                return $this->SendResponse($cart,'Product added to Bag Sucessfully');
            }

            //if product exist in rhe bag
            if(isset($cart[$product['id']])) {
                return $this->SendError('Product already exist in your bag');
            }
            // if item not exist in cart then add it to cart
            $cart[$id] = [
                'product_id'=>$product->id,
                'item_quantity'=>$request->item_quantity,
                'color'=> $request->color,
                'size' =>$request->size,
                'is_final_bag' =>'new'
            ];
            session()->put('cart', $cart);
            return $this->SendResponse($cart,'Product added to Bag ');
        }

        //if user was not a guest
        else if(auth('api')->user()){
            $user_id=auth('api')->user()->id;

        //search all products not ordered in user_bag
        $cart=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();

        foreach($cart as $item){

            if($item->product_id==$id)
            return $this->SendError('Product already exist in your bag');
        }
        try {
            $newItem=new User_bag();
            $newItem->user_id=$user_id;
            $newItem->product_id=$product->id;
            $newItem->item_quantity=$request->item_quantity;
            $newItem->color=$request->color;
            $newItem->size=$request->size;
            $newItem->is_final_bag='new';
            $newItem->save();
            return $this->SendResponse($newItem,'Product added to Bag Sucessfully');

        } catch (\Throwable $th) {
            return $this->SendError('Error to add product to your bag',$th->getMessage());
        }
        }

    }



    public function showProductBag($id)
    {
        try {
            $product=Product::findOrFail($id);
            if(is_null($product))
                return $this->sendError('product not founded');
            return $this->SendResponse($product, 'Product founded successfully');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function updateBag(Request $request,$itemId)
    {
        if(auth('api')->user()){

            $cartItem=User_bag::where('id',$itemId)->where('is_final_bag','new')->first();

            if ($cartItem) {
                $cartItem->item_quantity=$request->item_quantity;
                $cartItem->color=$request->color;
                $cartItem->size=$request->size;
                try {
                    $cartItem->save();
                    return $this->SendResponse($cartItem, 'product bag Updated Successfully!');
                } catch (\Throwable $th) {
                    return $this->SendError('cant\'t Update product in your bag',$th->getMessage());
                }
            }
        }
        else if(Auth::guest()){
            $cart = Session::get('cart');


            if(!$cart)
            return $this->sendError('cart sesssion not exist');
            try {

                $cart[$itemId]=[

                    'item_quantity'=>$request->item_quantity,
                    'color'=> $request->color,
                    'size' =>$request->size,
                ];
                session()->put('cart',$cart);
                return $this->SendResponse($cart[$itemId], 'Session product bag Updated Successfully!');

            } catch (\Throwable $th) {
                return $this->SendError('Error to update Sesssion',$th->getMessage());
            }

        }
    }


    public function deleteProductBag(Request $request,$idProduct)
    {
        if(auth('api')->user()){
            $cartItem=User_bag::findOrFail($idProduct);
            if(!$cartItem)
            return $this->sendError('product not founded in your bag');
            try {
                $cartItem->delete();
                return $this->SendResponse($cartItem, 'product deleted successfully');
            } catch (\Throwable $th) {
                return $this->SendError('Error to delete product',$th->getMessage());
            }
        }
        else if(Auth::guest()){
            $cart= Session::get('cart');
            if(is_null($cart))
                return $this->sendError('empty');
            try {
                unset($cart[$idProduct]); // Unset the index you want
                Session::put('cart',$cart); // Set the array again
                return $this->SendResponse($cart, 'product deleted successfully');
            } catch (\Throwable $th) {
                return $this->SendError('Error to delete product',$th->getMessage());
            }

        }
    }

    public function destroyBag(Request $request){
        if(auth('api')->user()){
            $user_id=auth('api')->user()->id;
            $cart=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();
            try {
                foreach($cart as $item){
                    $item->delete();
                }
                return $this->SendResponse($cart,'bag Deleted Sucessfully');
            } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
            }

        }
        else if(Auth::guest()){
            try {
                $request->session()->flush();
                return $this->SendResponse($request->session()->all(),'bag Deleted Sucessfully');
            } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
            }
        }

    }
}

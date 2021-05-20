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
            $user_id=Auth::id();
            $items=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();
            if($items->isEmpty())
                return $this->sendError('empty');
            $products = Product::whereHas('user_bags', function ($q) use($user_id) {
                $q->where('user_bags.user_id', $user_id);
            })->get();
            // $products=[];
            // foreach($items as $item){
            //     $products[]=[
            //         'id'=>$item->product_id,
            //     ];
            // }
            // $products_info=DB::table('products')->whereIn('id',$products)->get();
            //return items with products
            return $this->SendResponse([$items,$products],'bag list');
        }

    }

    public function addTobag(Request $request,$id){

        $product=Product::findOrFail($id);
        //dd($product);
        if(!$product){
            return $this->SendError('Product not found');
        }
        $user_id=Auth::id();
        //search all products not ordered in user_bag
        $cart=User_bag::where('user_id',$user_id)->where('is_final_bag','new')->get();

        foreach($cart as $item){

            if($item->product_id==$id)
            return $this->SendError('Product already exist in your bag');

        }
        try {
            $newItem=new User_bag();
            $newItem->user_id=$user_id;
            $newItem->product_id=$id;
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

            $user_id=Auth::id();
            $cartItem=User_bag::where('id',$itemId)->where('user_id',$user_id)->where('is_final_bag','new')->first();

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


    public function deleteProductBag(Request $request,$idProduct)
    {

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

    public function destroyBag(Request $request){

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
}

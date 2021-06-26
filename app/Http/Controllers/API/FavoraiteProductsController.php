<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Models\Favoraite_products;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FavoraiteProductsController extends BaseController
{
    public function myFavoraiteProducts()
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access');
            }
            else
            {
                $favoraiteproducts=DB::table('products')
                ->join('favoraite_products','favoraite_products.product_id','=','products.id')
                ->where('favoraite_products.user_id', $user->id)
                ->whereNull('products.deleted_at')
                ->select('products.*')
                ->get();
                if($favoraiteproducts->isEmpty())
                    return $this->sendError('Your favoraite products list is empty');
                return $this->SendResponse($favoraiteproducts,'User favoraite products list is retreived sucessfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function addProducttoFavoriate(Request $request)
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access');
            }
            else
            {
                $input=$request->all();
                $validator = Validator::make($input,[
                    'product_id'=>'required'
                ]);
                if( $validator->fails()) {
                    return $this->SendError('Validate Error',$validator->errors());
                }
                $favoraiteproducts=Favoraite_products::where('user_id',$user->id)->where('product_id',$request->product_id)->get();
                if($favoraiteproducts->count()!=0)
                    return $this->SendError('This product is added to the favoriate previously');
                $product=Product::where('id',$request->product_id)->first();
                if(is_null($product))
                    return $this->SendError('product is not found');
                $favoraiteproduct=Favoraite_products::create([
                    'user_id'=>Auth::id(),
                    'product_id'=>$request->product_id
                ]);
                return $this->SendResponse($favoraiteproduct, 'Product is added to the favoriate successfully!');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function removeProductfromFavoriate(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required'
            ]);
            if( $validator->fails()) {
                return $this->SendError('Validate Error',$validator->errors());
            }
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access');
            }
            else
            {
                $favoraiteproducts=Favoraite_products::where('user_id',$user->id)->where('product_id',$request->product_id)->get();
                if($favoraiteproducts->count()==0)
                    return $this->SendError('This product does not add to the favoriate');
                $product=Product::where('id',$request->product_id)->first();
                if(is_null($product))
                    return $this->SendError('product is not found');
                $favoraiteproduct=Favoraite_products::where('product_id',$request->product_id)
                            ->where('user_id',Auth::id())->delete();
                return $this->SendResponse($favoraiteproduct, 'Product is removed from faivorate successfully');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

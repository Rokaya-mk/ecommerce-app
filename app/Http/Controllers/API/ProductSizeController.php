<?php

namespace App\Http\Controllers\API;

use App\Models\Product_size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class ProductSizeController extends BaseController
{
    public function addSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size'=>'required|in:S,M,L,XL,XXL'
                ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=Product::find($request->product_id);
                if(is_null($product))
                    return $this->SendError('Product is not found');

                $sizefound = DB::table('product_sizes')->where('product_id',$request->product_id)->where('size',$request->size)->count();
                if($sizefound)
                    return $this->SendError('This size is found');

                $sizeId = DB::table('product_sizes')->where('product_id',$request->product_id)->max('id');

                $input['id']=$sizeId+1;
                $product_size=Product_size::create($input);
                return $this->SendResponse($product_size, 'Size is added Successfully!');
            }
            else
                return $this->SendError('You do not have rights to add this size');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function getProductSizes($id)
    {
        try
        {
            $product=Product::find($id);
            if(is_null($product))
                return $this->SendError('Product is not found');
            $productSizes=Product_size::where('product_id','=',$id)->get();
            if($productSizes->count()==0)
                return $this->SendError('This product does not have sizes');
            else
                return $this->SendResponse($productSizes, 'Sizes is retrieved Successfully!');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function deleteSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size'=>'required|in:S,M,L,XL,XXL'
            ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=Product::find($request->product_id);
                if(is_null($product))
                    return $this->SendError('Product is not found');
                $deletedRows=Product_size::where('product_id',$request->product_id)->where('size',$request->size)->delete();
                if($deletedRows==0)
                    return $this->SendError('size is not found');
                return $this->SendResponse($deletedRows, 'size is deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

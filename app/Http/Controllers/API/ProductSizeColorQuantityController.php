<?php

namespace App\Http\Controllers\API;

use App\Models\Product_size_color_quantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Product_size;
use App\Models\User;

class ProductSizeColorQuantityController extends BaseController
{
    public function addColorQuantityforCertainSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size_id'=>'required',
                'color'=>'required',
                'quantity'=>'required'
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
                $productSizes=Product_size::where('product_id','=',$request->product_id)->where('id','=',$request->size_id)->get();
                if($productSizes->count()==0)
                    return $this->SendError('This size is not found');

                $colorfound = DB::table('product_size_color_quantities')->where('product_id',$request->product_id)
                            ->where('size_id',$request->size_id)->where('color',$request->color)->count();
                if($colorfound)
                    return $this->SendError('This color is found');

                $colorId = DB::table('product_size_color_quantities')->where('product_id',$request->product_id)->where('size_id',$request->size_id)->max('id');

                $input['id']=$colorId+1;

                $product_color_quantity=Product_size_color_quantity::create($input);
                return $this->SendResponse($product_color_quantity, 'color and quantity are added Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function getProductColorQuantityofCertainSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size_id'=>'required'
                ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());

            $product=Product::find($request->product_id);
            if(is_null($product))
                return $this->SendError('Product is not found');
            $productSizes=Product_size::where('product_id','=',$request->product_id)->where('id','=',$request->size_id)->get();
            if($productSizes->count()==0)
                return $this->SendError('This size is not found');

            $colorQuantity=Product_size_color_quantity::where('product_id','=',$request->product_id)->where('size_id','=',$request->size_id)->get();
            if($colorQuantity->count()==0)
                return $this->SendError('This size does not have colors and quantities');
            else
                return $this->SendResponse($colorQuantity, 'colors and quantities are retrieved Successfully!');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }


    public function deleteColorofCertainSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size_id'=>'required',
                'color'=>'required'
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
                $productSizes=Product_size::where('product_id','=',$request->product_id)->where('id','=',$request->size_id)->get();
                if($productSizes->count()==0)
                    return $this->SendError('This size is not found');

                $deletedRows=Product_size_color_quantity::where('product_id',$request->product_id)->where('size_id',$request->size_id)->where('color','=',$request->color)->delete();
                if($deletedRows==0)
                    return $this->SendError('color is not found');
                return $this->SendResponse($deletedRows, 'color and quantity are deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }


    public function updateColorQuantityofCertainSize(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'size_id'=>'required',
                'color_id'=>'required',
                'color'=>'required',
                'quantity'=>'required'
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
                $productSizes=Product_size::where('product_id','=',$request->product_id)->where('id','=',$request->size_id)->get();
                if($productSizes->count()==0)
                    return $this->SendError('This size is not found');

                $updatedRows=Product_size_color_quantity::where('product_id',$request->product_id)->where('size_id',$request->size_id)->where('id',$request->color_id)->update(['color'=>$request->color, 'quantity'=>$request->quantity]);
                if($updatedRows==0)
                    return $this->SendError('color and quantity are not updated');
                return $this->SendResponse($updatedRows, 'color and quantity are updated Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

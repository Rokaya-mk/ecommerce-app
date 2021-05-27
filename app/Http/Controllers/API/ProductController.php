<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    public function index()
    {
        try
        {
            $products=Product::all();
            if($products->count()==0)
                return $this->SendError('There is no products');
            return $this->SendResponse($products, 'Products is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function show($id)
    {
        try
        {
            $product=Product::find($id);
            if(is_null($product))
                return $this->SendError('Product is not found');
            return $this->SendResponse($product, 'Product is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
            'name_en'=>'required|unique:products',
            'name_ar'=>'required|unique:products',
            'description_en'=>'required',
            'description_ar'=>'required',
            'price'=>'required',
            ]);
            if( $validator->fails())
            return $this->SendError('Validate Error',$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=product::create($input);
                return $this->SendResponse($product, 'product is added Successfully!');
            }
            else
                return $this->SendError('You do not have rights to add this product');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try
        {
            $input = $request->all();
            $validator = Validator::make($input , [
            'name_en'=>'required',
            'name_ar'=>'required',
            'description_en'=>'required',
            'description_ar'=>'required',
            'price'=>'required'
            ]);
            if ($validator->fails())
                return $this->SendError('Please validate error' ,$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=Product::find($id);
                if(is_null($product))
                    return $this->SendError('Product is not found');
                $product->name_en=$input['name_en'];
                $product->name_ar=$input['name_ar'];
                $product->description_en=$input['description_en'];
                $product->description_ar=$input['description_ar'];
                $product->price=$input['price'];
                $product->save();
                return $this->SendResponse($product, 'Product is updated Successfully!');
            }
            else
                return $this->SendError('You do not have rights to update this product');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function deleteProduct(Request $request)
    {
        try
        {
            $input = $request->all();
            $validator = Validator::make($input , [
            'product_id'=>'required'
            ]);
            if ($validator->fails())
                return $this->SendError('Please validate error' ,$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=Product::find($request->product_id);
                if(is_null($product))
                    return $this->SendError('Product is not found');
                $product->delete();
                return $this->SendResponse($product, 'Product is deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function searchForProduct(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
            'name'=>'required'
            ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());
            $products = DB::table('products')
            ->where('name_en', 'like', '%' . $input['name'] . '%')
            ->orWhere('name_ar', 'like', '%' . $input['name'] . '%')->get();
            if($products->count()==0)
                return $this->SendError('Product is not foundو Search using another keyword');
            return $this->SendResponse($products, 'Product is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

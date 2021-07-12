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

    public function showProductsforUsers()
    {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=0){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $favoraiteproducts=DB::table('products')
                ->whereIn('id', function($query){
                    $query->select('product_id')
                    ->from('favoraite_products')
                    ->whereNull('products.deleted_at')
                    ->where('favoraite_products.user_id', Auth::id());
                })
                ->select(DB::raw('products.*'))
                ->get();
                foreach($favoraiteproducts as $item)
                    $item->inFavoriate = "1" ;

                $notFavoraiteproducts=DB::table('products')
                ->whereNotIn('id', function($query){
                    $query->select('product_id')
                    ->from('favoraite_products')
                    ->where('favoraite_products.user_id', Auth::id());
                })
                ->select(DB::raw('products.*'))
                ->whereNull('products.deleted_at')
                ->get();
                foreach($notFavoraiteproducts as $item)
                    $item->inFavoriate = "0" ;
                $allProducts =$favoraiteproducts->merge($notFavoraiteproducts)->sortBy('id');
                return $this->SendResponse($allProducts,'Products is retrieved Successfully!');
            }
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

    // public function deleteProduct($id)
    // {
    //     try
    //     {
    //         $userId=Auth::id();
    //         $user=User::find($userId);
    //         if($user->is_Admin == 1)
    //         {
    //             $product=Product::find($id);
    //             if(is_null($product))
    //                 return $this->SendError('Product is not found');
    //             $product->delete();
    //             return $this->SendResponse($product, 'Product is deleted Successfully!');
    //         }
    //         else
    //             return $this->SendError('You do not have rights to delete this product');
    //     } catch (\Throwable $th) {
    //         return $this->SendError('Error',$th->getMessage());
    //     }
    // }

    public function softDeleteProduct($id)
    {
        try
        {
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $product=Product::find($id);
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
    public function showDeletedProducts() {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $deletedProducts= Product::onlyTrashed()->get();
                return $this->SendResponse($deletedProducts,'Deleted products is retrieved Successfully!');
            }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    public function restoreDeletedProduct($id) {
        try {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
            {
                $deletedProduct= Product::onlyTrashed()->where('id', $id)->first();
                if(is_null($deletedProduct))
                    return $this->SendError('Product is not found');
                else
                {
                    $deletedProduct->restore();
                    return $this->SendResponse($deletedProduct,'Deleted products is restored Successfully!');
                }
            }
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
            ->whereNull ('deleted_at')
            ->orWhere('name_ar', 'like', '%' . $input['name'] . '%')
            ->whereNull ('deleted_at')->get();
            if($products->count()==0)
                return $this->SendError('Product is not foundو Search using another keyword');
            return $this->SendResponse($products, 'Product is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
    public function searchForDeletedProduct(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
            'name'=>'required'
            ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());
            $products = Product::onlyTrashed()->get();
            $products = DB::table('products')
            ->where('name_en', 'like', '%' . $input['name'] . '%')
            ->whereNotNull ('deleted_at')
            ->orWhere('name_ar', 'like', '%' . $input['name'] . '%')
            ->whereNotNull ('deleted_at')->get();
            if($products->count()==0)
                return $this->SendError('Product is not foundو Search using another keyword');
            return $this->SendResponse($products, 'Product is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

}

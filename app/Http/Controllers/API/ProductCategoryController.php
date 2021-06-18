<?php

namespace App\Http\Controllers\API;

use App\Models\Product_category;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProductCategoryController extends BaseController
{
    public function showAllProductCategories($id)
    {
        try
        {
            $product=Product::find($id);
            if(is_null($product))
                return $this->SendError('product is not found');
            $productCatergories=DB::table('product_categories')
            ->join('categories','categories.id','=','product_categories.category_id')
            ->where('product_categories.product_id', $id)
            ->select('category_name')
            ->get();

            if($productCatergories->count()==0)
                return $this->SendError('This product dose not have Category');
            return $this->SendResponse($productCatergories, 'product catogaries are retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function addCategorytoProduct(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'category_name'=>'required'
            ]);
            if( $validator->fails()) {
                return $this->SendError('Validate Error',$validator->errors());
            }
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $productCategories=DB::table('product_categories')
                ->join('categories','categories.id','=','product_categories.category_id')
                ->select('categories.id','category_name')
                ->where('product_id',$request->product_id)
                ->where('category_name',$request->category_name)
                ->get();
                if($productCategories->count()!=0)
                    return $this->SendError('This product has this Category previously');
                $pCategory=Category::where('category_name',$request->category_name)->first();
                if(is_null($pCategory))
                    return $this->SendError('Category is not found');
                $product_category=Product_category::create([
                    'product_id'=>$request->product_id,
                    'category_id'=>$pCategory->id
                ]);
                return $this->SendResponse($product_category, 'product ia add to this category Successfully!');
            }
            else
                return $this->SendError('You do not have rights to add this category');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function removeProductfromCategory(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'category_name'=>'required'
            ]);
            if( $validator->fails()) {
                return $this->SendError('Validate Error',$validator->errors());
            }
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $productCategories=DB::table('product_categories')
                ->join('categories','categories.id','=','product_categories.category_id')
                ->select('category_name')
                ->where('product_id',$request->product_id)
                ->where('category_name',$request->category_name)
                ->get();
                if($productCategories->count()==0)
                    return $this->SendError('This product does not have this Category');
                $pCategory=Category::where('category_name',$request->category_name)->first();
                if(is_null($pCategory))
                    return $this->SendError('Category is not found');
                $prod_category = Product_category::where('product_id',$request->product_id)
                                ->where('category_id',$pCategory->id)->delete();
                return $this->SendResponse($prod_category, 'category is deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this category');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

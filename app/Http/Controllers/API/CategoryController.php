<?php

namespace App\Http\Controllers\API;

use App\Models\Product_category;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CategoryController extends BaseController
{
    public function index()
    {
        try
        {
            $catogaries=Category::all();
            if($catogaries->count()==0)
                return $this->SendError('There is no catogaries');
            return $this->SendResponse($catogaries, 'catogaries is retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function show($id)
    {
        try
        {
            $category=Category::find($id);
            if(is_null($category))
                return $this->SendError('Category is not found');
            return $this->SendResponse($category, 'Category is retrieved Successfully!');
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
                'category_name'=>'required|unique:categories,category_name'
            ]);
            if( $validator->fails()) {
            return $this->SendError('Validate Error',$validator->errors());
            }
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $category=Category::create($input);
                return $this->SendResponse($category, 'Category is added Successfully!');
            }
            else
                return $this->SendError('You do not have rights to add this category');
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
                'category_name'=>'required|unique:categories,category_name',
            ]);
            if ($validator->fails()) {
                return $this->SendError('Please validate error' ,$validator->errors() );
            }
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $category=Category::find($id);
                if(is_null($category))
                    return $this->SendError('Category is not found');
                $category->category_name=$input['category_name'];
                $category->save();
                return $this->SendResponse($category, 'Category is updated Successfully!');
        }
        else
            return $this->SendError('You do not have rights to update this category');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function deleteCategory(Request $request)
    {
        try
        {
            $input = $request->all();
            $validator = Validator::make($input ,[
                'category_id'=>'required'
            ]);
            if ($validator->fails())
                return $this->SendError('Please validate error' ,$validator->errors());
            $userId=Auth::id();
            $user=User::find($userId);
            if($user->is_Admin == 1)
            {
                $prod_category=Product_category::where('category_id',$request->category_id)->first();
                if(! is_null($prod_category))
                    return $this->SendError('You can not delete this category, because it contains products. Please remove this products from this category in order to delete it');
                $category=Category::find($request->category_id);
                if(is_null($category))
                    return $this->SendError('Category is not found');
                $category->delete();
                return $this->SendResponse($category, 'Category is deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this category_id');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Models\Product_image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class ProductImageController extends BaseController
{
    public function addImage(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'image'=>'required|mimes:jpg,jpeg,png|max:5048'  //max is max image size in KB
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

                $newImageName=time() . '-' . $request->image->getClientOriginalName();
                $imageId = DB::table('product_images')->where('product_id',$request->product_id)->max('id');

                $input['id']=$imageId+1;
                $request->image->move(public_path("/images"),$newImageName);
                $imageURL=url('/images'.'/'.$newImageName);
                $input['image_url']= $imageURL;
                $product_image=Product_image::create($input);
                return $this->SendResponse($product_image, 'image is added Successfully!');
            }
            else
                return $this->SendError('You do not have rights to update this product');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function getProductImagesUrls($id)
    {
        try
        {
            $product=Product::find($id);
            if(is_null($product))
                return $this->SendError('Product is not found');
            $productImagesUrls=Product_image::where('product_id',$id)->get();
            if($productImagesUrls->count()==0)
                return $this->SendError('This product does not have images');
            else
                return $this->SendResponse($productImagesUrls, 'images urls is retrieved Successfully!');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function deleteImage(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
                'product_id'=>'required',
                'image_id'=>'required'
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
                $deletedRows=Product_image::where('product_id',$request->product_id)->where('id',$request->image_id)->delete();
                if($deletedRows==0)
                    return $this->SendError('image is not found');
                return $this->SendResponse($deletedRows, 'image is deleted Successfully!');
            }
            else
                return $this->SendError('You do not have rights to delete this product');
        }catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }
}

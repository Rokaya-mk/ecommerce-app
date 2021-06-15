<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\User_bag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use File;

class ReviewController extends BaseController
{
    public function getReview(Request $request){
        try {
            if (!Product::where('id',$request->product_id)->first()) {
                return $this->SendError('Product not found');
            }
            $reviews = Review::where('product_id', $request->product_id)->get();
            if ($reviews->count() == 0) {
                return $this->SendError('There is no review');
            }
            return $this->SendResponse($reviews, 'All review recived successfully');
        } catch (\Throwable $th) {
            $this->SendError('Something went wrong', $th->getMessage());
        }
    }

    public function addReview(Request $request){
        $input = $request->all();
        $validate = Validator::make($input, [
            'product_id' => 'required',
            'rate' => 'required|min:1|max:5',
            'comment' => 'required',
            'photo' => 'nullable|mimes:jpg,jpeg,png|max:5048',
        ]);
        if ($validate->fails()) {
            return $this->SendError('Validate error', $validate->errors());
        }
        $product = Product::findOrFail($request->product_id);
        if (!$product) {
            return $this->SendError('Product not found');
        }
        $user_bag = User_bag::where('product_id', $request->product_id)->where('user_id', Auth::id())->get();
        foreach ($user_bag as $item) {
            if ($item->is_final_bag == 'new') {
                return $this->SendError('You can not review this product');
            }
        }
        if (Review::all()->count() > 0) {
            $reviewsCheck = Review::where('product_id', $request->product_id)->get();
            foreach ($reviewsCheck as $reviewCheck) {
                if ($reviewCheck->user_id == Auth::id()) {
                    return $this->SendError('You already reviewed this product');
                }
            }
        }
        $input['user_id'] = Auth::id();
        if ($request->has('photo')) {
            $newImageName = time() . "_" . $request->photo->getClientOriginalName();
            $request->photo->move('uploads/ReviewPics', $newImageName);
            $imageURL = url('uploads/ReviewPics' . '/' . $newImageName);
            $input['photo'] = $imageURL;
        }
        $review = Review::create($input);

        return $this->SendResponse($review, 'Review added successfully');
    }

    public function editReview(Request $request, $id){
        $input = $request->all();
        $validate = Validator::make($input, [
            'product_id' => 'required',
            'rate' => 'required|min:1|max:5',
            'comment' => 'required',
            'photo' => 'nullable|mimes:jpg,jpeg,png|max:5048',
        ]);
        if ($validate->fails()) {
            return $this->SendError('Validate error', $validate->errors());
        }
        $product = Product::findOrFail($request->product_id);
        if (!$product) {
            return $this->SendError('Product not found');
        }
        $review = Review::find($id);
        if ($review->user_id != Auth::id()) {
            return $this->SendError('You do not have permission');
        }
        $review->rate = $request->rate;
        $review->comment = $request->comment;
        if ($request->has('photo')) {
            $newImageName = time() . "_" . $request->photo->getClientOriginalName();
            $oldImage = substr($review->photo, strlen($request->url()));
            File::delete(public_path($oldImage));
            $request->photo->move('uploads/ReviewPics', $newImageName);
            $imageURL = url('uploads/ReviewPics' . '/' . $newImageName);
            $review->photo = $imageURL;
        }
        $review->save();
        return $this->SendResponse($review, 'Review updated successfully');
    }

    public function deleteReview(Request $request, $id){
        $review = Review::findOrFail($id);
        if ($review->user_id == Auth::id()) {
            $review->delete($id);
            return $this->SendResponse($review, 'Review deleted successfully');
        }
        return $this->SendError('You do not have permisson to delete the review');
    }
}

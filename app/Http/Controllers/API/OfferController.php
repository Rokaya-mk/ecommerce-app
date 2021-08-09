<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends BaseController
{
    //display offers
    public function offers(){
        try
        {
            $offers=Offer::all();
            if($offers->count()==0)
                return $this->SendError('There is no Offer');
            return $this->SendResponse($offers, 'Offers are retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //add new offer
    public function addNewOffer(Request $request){
        try {
            //validate data
        $validateData=Validator::make($request->all(), [
            'product_id' =>'required|unique:offers,product_id',
            'offer_product_price'=>'required|numeric',
            'offer_start_date'=>'required|date',
            'offer_expired_date'=>'required|date',

        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());
        $user=User::find(Auth::id());
        if($user->is_Admin != 1){
            return $this->SendError('You do not have rights to add coupon');
        }else{
            //find product
            $product=Product::find($request->product_id);
            if(is_null($product)){
                return $this->SendError('this product not founded');
            }
            // time now
            $date=Carbon::now();
            $offer=new Offer();
            $offer->product_id=$product->id;
            $product_price_offer=$request->offer_product_price;
            $offer->offer_product_price=(double)($product_price_offer);
            $offer->offer_start_date= Carbon::parse($request->offer_start_date)->format('Y-m-d H:i:s');
            $offer->offer_expired_date= Carbon::parse($request->offer_expired_date)->format('Y-m-d H:i:s');
            if($offer->offer_start_date < $date)
                return $this->SendError('Start date of offer should be grater then time now');
            if($offer->offer_expired_date < $offer->offer_start_date)
                return $this->SendError('experired date can not be less then started date of offer');
            $offer->save();
            return $this->SendResponse($offer,'offer created Successfully !');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    public function updateOffer(Request $request,$idOffer){
        try {

            //validate data
        $validateData=Validator::make($request->all(), [
            'offer_product_price'=>'required',
            'offer_start_date'=>'required|date',
            'offer_expired_date'=>'required|date',

        ]);
        if ($validateData->fails())
                return $this->SendError(' Invalid data' ,$validateData->errors());
        $user=User::find(Auth::id());
        if($user->is_Admin != 1){
            return $this->SendError('You do not have rights to add coupon');
        }else{
            $offer=Offer::find($idOffer);
            if(is_null($offer))
                return $this->SendError('offer not founded');
            $offer->offer_product_price=$request->offer_product_price;
            $offer->offer_start_date= Carbon::parse($request->offer_start_date)->format('Y-m-d H:i:s');
            $offer->offer_expired_date= Carbon::parse($request->offer_expired_date)->format('Y-m-d H:i:s');
            $date=Carbon::now();
            if($offer->offer_start_date < $date)
                return $this->SendError('Start date of offer should be grater then time now');
            if($offer->offer_expired_date < $offer->offer_start_date)
                return $this->SendError('experired date can not be less then started date of offer');
            $offer->save();
            return $this->SendResponse($offer,'offer updated Successfully !');
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }
    }

    //delete offer
    public function destroyOffer($idOffer)
    {
        try {
            $user=User::find(Auth::id());
        if($user->is_Admin != 1){
            return $this->SendError('You do not have rights to update offer');
        }else{
            $offer = Offer::find($idOffer);
        if(is_null($offer))
            return $this->SendError('offer not founded');
        if ($offer) {
            $offer->delete();
            return $this->SendResponse($offer,'offer Deleted Sucessfully');
            }
        }
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

    }

    public function productsOffers(){
        try {
            $offers=Offer::all();
            if($offers->isEmpty())
                return $this->SendError('there is no offer');
            $productsOffers=[];
            foreach($offers as $offer){
                //$product=Product::find($offer->product_id);
                $product=Product::with('offer')->where('id',$offer->product_id)->get();
                array_push($productsOffers,$product);
            }
            return $this->SendResponse(['ProductsOffered' => $productsOffers],'products offered are retrieved Successfully!');
        } catch (\Throwable $th) {
            return $this->SendError('Error',$th->getMessage());
        }

    }
}

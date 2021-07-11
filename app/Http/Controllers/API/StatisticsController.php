<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatisticsController extends BaseController
{
    // public function productsCount()
    // {
    //     try
    //     {
    //         $user=User::find(Auth::id());
    //         if($user->is_Admin !=1){
    //             return $this->sendError('You do not have rights to access');
    //         }
    //         else
    //          {
    //             $ProductsCount = DB::table('product_size_color_quantities')
    //               ->sum('quantity');
    //             return $this->SendResponse($ProductsCount, 'Products count is retrieved Successfully!');
    //          }
    //     } catch (\Throwable $th) {
    //             return $this->SendError('Error',$th->getMessage());
    //     }
    // }

    public function allProductsTypeCount()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $all = DB::table('products')
                //->whereNull('products.deleted_at')
                ->count(DB::raw('DISTINCT products.id'));
                return $this->SendResponse($all, 'All Products Type Count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function avaliableProductsTypeCount()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $allProductsTypeCount = DB::table('product_size_color_quantities')
                ->join('products','products.id','=','product_size_color_quantities.product_id')
                //->whereNull('products.deleted_at')
                ->count(DB::raw('DISTINCT product_size_color_quantities.product_id'));
                return $this->SendResponse($allProductsTypeCount, 'Products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function soldProductsCount()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $SoldProductsCount = DB::table('user_bags')
                  ->where('is_final_bag', 'old')
                  ->sum('item_quantity');
                return $this->SendResponse($SoldProductsCount, 'Sold products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function wallet() {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $SoldProductsCount = DB::table('payments')
                  ->sum('amount_paid');
                return $this->SendResponse($SoldProductsCount, 'Wallet (payment ammount) is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function quantitybyProductName()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $quantitybyProductName = DB::table('product_size_color_quantities')
                ->join('products','products.id','=','product_size_color_quantities.product_id')
                ->select('name_en', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('name_en')
                ->get();
                return $this->SendResponse($quantitybyProductName, 'Quantity by Product Name is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function todaySoldProductsCount()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $today =Carbon::now();
                $todaDateonly=$today->toDateString();
                $todayTime00=Carbon::createFromFormat('Y-m-d H:i', $todaDateonly.' 00:00');
                $todayTime23_59=Carbon::createFromFormat('Y-m-d H:i', $todaDateonly.' 23:59');

                $SoldProductsCount = DB::table('user_bags')
                  ->where('is_final_bag', 'old')
                  ->where('updated_at', '>=',$todayTime00)
                  ->where('updated_at', '<=',$todayTime23_59)
                  ->sum('item_quantity');
                return $this->SendResponse($SoldProductsCount, 'Sold products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function lastWeekSoldProductsCounts()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $SoldProducts=[];
                 for($i=6; $i>=0; $i--)
                 {
                    $today =Carbon::now();
                    $DayofWeek=$today->addDays(-1*$i);
                    $DayofWeekDateonly=$DayofWeek->toDateString();
                    $DayofWeekDateTime00=Carbon::createFromFormat('Y-m-d H:i', $DayofWeekDateonly.' 00:00');
                    $DayofWeekDateTime23_59=Carbon::createFromFormat('Y-m-d H:i', $DayofWeekDateonly.' 23:59');
                    $SoldProductsCount = DB::table('user_bags')
                    ->where('is_final_bag', 'old')
                    ->where('updated_at', '>=',$DayofWeekDateTime00)
                    ->where('updated_at', '<=',$DayofWeekDateTime23_59)
                    ->sum('item_quantity');
                    $SoldProducts[] = [
                        'day'=>    7- $i,
                        'SoldProductsCount'=> (int)$SoldProductsCount,
                        'DayofWeekDate'=> $DayofWeekDateonly
                    ];
                 }

                return $this->SendResponse($SoldProducts, 'Sold products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function lastMonthSoldProductsCounts()
    {
        try
        {
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $SoldProducts=[];
                 for($i=29; $i>=0; $i--)
                 {
                    $today =Carbon::now();
                    $DayofMonth=$today->addDays(-1*$i);
                    $DayofMonthDateonly=$DayofMonth->toDateString();
                    $DayofMonthDateTime00=Carbon::createFromFormat('Y-m-d H:i', $DayofMonthDateonly.' 00:00');
                    $DayofMonthDateTime23_59=Carbon::createFromFormat('Y-m-d H:i', $DayofMonthDateonly.' 23:59');
                    $SoldProductsCount = DB::table('user_bags')
                    ->where('is_final_bag', 'old')
                    ->where('updated_at', '>=',$DayofMonthDateTime00)
                    ->where('updated_at', '<=',$DayofMonthDateTime23_59)
                    ->sum('item_quantity');
                    $SoldProducts[] = [
                        'day'=>    30- $i,
                        'SoldProductsCount'=> (int)$SoldProductsCount,
                        'DayofMonthDate'=> $DayofMonthDateonly
                    ];
                 }

                return $this->SendResponse($SoldProducts, 'Sold products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    public function yearByMonthSoldProductsCounts(Request $request)
    {
        try
        {
            $input=$request->all();
            $validator = Validator::make($input,[
            'year'=>'required'
            ]);
            if( $validator->fails())
                return $this->SendError('Validate Error',$validator->errors());
            $user=User::find(Auth::id());
            if($user->is_Admin !=1){
                return $this->sendError('You do not have rights to access');
            }
            else
             {
                $today =Carbon::now();

                $month=$today->month;
                $year=$input['year'];
                $SoldProducts=[];
                 for($i=1; $i<=12; $i++)
                 {
                    $month= $i;
                    $firstDayDateonly=$year.'-'.$month.'-01';
                    $lastDayDateonly=$year.'-'.$month.'-31';
                    $DayofWeekDateTime00=Carbon::createFromFormat('Y-m-d H:i', $firstDayDateonly.' 00:00');
                    $DayofWeekDateTime23_59=Carbon::createFromFormat('Y-m-d H:i', $lastDayDateonly.' 23:59');
                    $SoldProductsCount = DB::table('user_bags')
                    ->where('is_final_bag', 'old')
                    ->where('updated_at', '>=',$DayofWeekDateTime00)
                    ->where('updated_at', '<=',$DayofWeekDateTime23_59)
                    ->sum('item_quantity');
                    $SoldProducts[] = [
                        'month'=>    $i,
                        'SoldProductsCount'=> (int)$SoldProductsCount
                    ];
                 }

                return $this->SendResponse($SoldProducts, 'Sold products count is retrieved Successfully!');
             }
        } catch (\Throwable $th) {
                return $this->SendError('Error',$th->getMessage());
        }
    }

    // public function LastWeekSoldProductsCount()
    // {
    //     try
    //     {
    //         $user=User::find(Auth::id());
    //         if($user->is_Admin !=1){
    //             return $this->sendError('You do not have rights to access');
    //         }
    //         else
    //          {
    //             $today =Carbon::now();
    //             $todaDateonly=$today->toDateString();
    //             $firstDayatWeek=$today->addDays(-7);
    //             $firstDayatWeekDateonly=$firstDayatWeek->toDateString();
    //             $firstDayatWeekTime00=Carbon::createFromFormat('Y-m-d H:i', $firstDayatWeekDateonly.' 00:00');
    //             $todayTime23_59=Carbon::createFromFormat('Y-m-d H:i', $todaDateonly.' 23:59');

    //             $SoldProductsCount = DB::table('user_bags')
    //               ->where('is_final_bag', 'old')
    //               ->where('updated_at', '>=',$firstDayatWeekTime00)
    //               ->where('updated_at', '<=',$todayTime23_59)
    //               ->sum('item_quantity');
    //             return $this->SendResponse($SoldProductsCount, 'Last week sold products count is retrieved Successfully!');
    //          }
    //     } catch (\Throwable $th) {
    //             return $this->SendError('Error',$th->getMessage());
    //     }
    // }

    // public function LastMonthSoldProductsCount()
    // {
    //     try
    //     {
    //         $user=User::find(Auth::id());
    //         if($user->is_Admin !=1){
    //             return $this->sendError('You do not have rights to access');
    //         }
    //         else
    //          {
    //             $today =Carbon::now();
    //             $todaDateonly=$today->toDateString();
    //             $firstDayatMonth=$today->addDays(-30);
    //             $firstDayatWeekDateonly=$firstDayatMonth->toDateString();
    //             $firstDayatMonthTime00=Carbon::createFromFormat('Y-m-d H:i', $firstDayatWeekDateonly.' 00:00');
    //             $todayTime23_59=Carbon::createFromFormat('Y-m-d H:i', $todaDateonly.' 23:59');

    //             $SoldProductsCount = DB::table('user_bags')
    //               ->where('is_final_bag', 'old')
    //               ->where('updated_at', '>=',$firstDayatMonthTime00)
    //               ->where('updated_at', '<=',$todayTime23_59)
    //               ->sum('item_quantity');
    //             return $this->SendResponse($SoldProductsCount, 'Last month sold products count is retrieved Successfully!');
    //          }
    //     } catch (\Throwable $th) {
    //             return $this->SendError('Error',$th->getMessage());
    //     }
    // }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'hasCoupon',
        'money_payement',
        'is_order_sent',
        'unique_order_id'
    ];
    public function users(){
        return $this->belongsTo('App\Models\User');
    }
    public function coupons(){
        return $this->belongsTo('App\Models\Coupon');
    }
    public function payment(){
        return $this->hasOne('App\Models\Payment');
    }

}

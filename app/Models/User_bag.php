<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_bag extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'product_id',
        'order_id',
        'item_quantity',
        'color',
        'size',
        'product_price',
        'has_offer',
        'price_after_offer'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function products(){
        return $this->belongsTo('App\Models\Product');
    }

    public function order(){
        return $this->belongsTo('App\Models\Order');
    }


}

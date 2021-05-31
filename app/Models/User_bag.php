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
        'is_final_bag'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function products(){
        return $this->hasMany('App\Models\Product');
    }

    public function order(){
        return $this->belongsTo('App\Models\Order');
    }
}

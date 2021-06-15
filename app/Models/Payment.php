<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','order_id','payment_method','amount_paid'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function order(){
        return $this->belongsTo('App\Models\Order');
    }
}

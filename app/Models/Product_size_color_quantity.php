<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_size_color_quantity extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size_id',
        'id',
        'color',
        'quantity'
    ];

    public function product_size()
    {
    	return $this->belongsTo('App\Models\Product_size');
    }

}

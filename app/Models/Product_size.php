<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_size extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'id',
        'size',
    ];

    public function product()
    {
    	return $this->belongsTo('App\Models\Product');
    }

    public function Product_size_color_quantity()
    {
    	return $this->hasMany('App\Models\Product_size_color_quantity',);
    }
}

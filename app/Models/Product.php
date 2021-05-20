<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price',
    ];

    public function Product_image()
    {
    	return $this->hasMany('App\Models\Product_image');
    }

    public function Product_size()
    {
    	return $this->hasMany('App\Models\Product_size');
    }

    public function user_bags(){
        return $this->hasMany('App\Models\User_bag');
    }
}

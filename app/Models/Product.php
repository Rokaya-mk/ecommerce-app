<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price',
    ];
    protected $dates = ['deleleted_at'];

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
    public function Product_category()
    {
    	return $this->hasMany('App\Models\Product_category');
    }
}

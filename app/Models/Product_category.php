<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_category extends Model
{
    use HasFactory;
    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'category_id',
    ];


    public function product()
    {
    	return $this->belongsTo('App\Models\Product');
    }

    public function category()
    {
    	return $this->belongsTo('App\Models\Category');
    }
}

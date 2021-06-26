<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favoraite_products extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'product_id'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function products()
    {
        return $this->belongsTo('App\Models\Product');
    }

}

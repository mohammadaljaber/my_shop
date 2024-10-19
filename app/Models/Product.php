<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    protected $guarded=[];

    public function coupons(): MorphMany
    {
        return $this->morphMany(Coupon::class, 'couponable');
    }

    public function stocks(){
        return $this->hasMany(Stock::class,'product_id');
    }
    
}

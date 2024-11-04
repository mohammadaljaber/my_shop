<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferItem extends Model
{
    protected $guarded=[];
    public function offer(){
        return $this->belongsTo(Offer::class,'offer_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}

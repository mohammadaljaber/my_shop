<?php

namespace App\Http\Controllers\Repositories\Orders;

use App\Models\Order;
use App\Models\Offer;
use App\Models\Stock;
use App\Http\Controllers\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OrdersRepository extends BaseRepository{

    public function __construct( Order $model)
    {
        parent::__construct($model);
    }

    public function index(){
        return auth()->user->orders;
    }

    public function store($data):Model
    {
        $order=Order::create([
            'costumer_id'=>auth()->user()->id,
            'total_price'=>0
        ]);
        $total_price=$this->storeOrderItems($data,$order);

        $order->total_price=$total_price;
        $order->update();
        return $order;
    }


    private function storeOrderItems($items,Order $order){
        $total_price=0;
        foreach($items->products as $item){
            $quantity=$item['count'];
            $product=Stock::find($item['id']);
            $total_price+=$this->attachProductToOrderItems($order,$product,$quantity);
        }
        $total_price+=$this->attachOffersToOrderItems($order,$items->offers);
        return $total_price;
    }

    private function attachProductToOrderItems(Order $order,Stock $product,$quantity=1){
        $productPrice=$product->price;
        ($productPrice!=null)?:$productPrice=$product->product->price;
        $order->contents()->create([
            'quantity'=>$quantity,
            'price'=>$productPrice*$quantity,
            'contentable_id'=>$product->id,
            'contentable_type'=>Stock::class
        ]);
        $product->quantity-=$quantity;
        $product->update();
        return $productPrice*$quantity;
    }

    private function attachOffersToOrderItems(Order $order,$offers){
        $offerPrice=0;
        foreach($offers as $off){
            $offer=Offer::find($off['id']);
            $offerPrice+=$offer->price;
            $order->contents()->create([
                'quantity'=>1,
                'price'=>$offer->price,
                'contentable_id'=>$offer->id,
                'contentable_type'=>Offer::class,
                'stocks'=>json_encode($off['stocks'])
            ]);
            foreach($off['stocks'] as $stock){
                $stock=Stock::find($stock);
                $stock->quantity-=1;
                $stock->update();
            }
        }
        return $offerPrice;
    }

}




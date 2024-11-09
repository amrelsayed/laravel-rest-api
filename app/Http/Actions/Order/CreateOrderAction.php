<?php

namespace App\Http\Actions\Order;

use App\Models\Order;
use App\Models\Product;

class CreateOrderAction
{
    public function exectue(array $data): Order
    {
        $orderPrice = 0;
        $products = [];

        foreach ($data['products'] as $productData) {
            $product = Product::find($productData['id']);

            $product->reduceStock($productData['quantity']);

            $orderPrice += $product->price * $productData['quantity'];

            $products[$product->id] = ['quantity' => $productData['quantity']];
        }

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_price' => $orderPrice,
            'status' => Order::STATUS['Placed']
        ]);

        $order->products()->sync($products);
        $order->save();

        return $order;
    }
}
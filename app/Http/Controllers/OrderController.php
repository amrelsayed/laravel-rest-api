<?php

namespace App\Http\Controllers;

use App\Http\Actions\Order\CreateOrderAction;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use DB;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, CreateOrderAction $createOrderAction)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $order = $createOrderAction->execute($data);

            DB::commit();

            $order->load(['user', 'products']);

            return response()->json([
                'message' => 'Order success',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Order failed',
                'error' => $ex->getMessage(),
            ]);
        }
    }
}

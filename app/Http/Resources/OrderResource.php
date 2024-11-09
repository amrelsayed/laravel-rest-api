<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "total_price" => $this->total_price,
            "status" => Order::STATUS[$this->status],
            "user" => new UserResource($this->whenLoaded("user")),
            "products" => ProductResource::collection($this->whenLoaded("products")),
        ];
    }
}

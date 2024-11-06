<?php
namespace App\Http\Actions\Product;

use App\Models\Product;
use Illuminate\Http\Request;

class ListProductsAction
{
    public function execute(Request $request)
    {
        $query = Product::query();

        if ($request->has("product_name")) {
            $query = $query->where("name", "like", $request->product_name . "%");
        }

        if ($request->has("category_id")) {
            $query = $query->where("category_id", $request->category_id);
        }

        if ($request->has("price_from")) {
            $query = $query->where("price", '>=', $request->price_from);
        }

        if ($request->has("price_to")) {
            $query = $query->where("price", '<=', $request->price_to);
        }

        if ($request->has("stock_from")) {
            $query = $query->where("stock", '>=', $request->stock_from);
        }

        if ($request->has("stock_to")) {
            $query = $query->where("stock", '<=', $request->stock_to);
        }

        $query->with('category')
            ->orderBy('name');

        return $query->paginate();
    }
}
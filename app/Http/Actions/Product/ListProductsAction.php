<?php
namespace App\Http\Actions\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListProductsAction
{
    public function execute(Request $request)
    {
        $cacheKey = $this->getCacheKey($request);

        $products = Cache::remember($cacheKey, 60 * 60, function () use ($request) {
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
        });

        return $products;
    }

    private function getCacheKey($request): string
    {
        $key = 'products:';

        $query_params = $request->query();

        ksort($query_params);

        $query_str = http_build_query($query_params);

        return $key . $query_str;
    }
}
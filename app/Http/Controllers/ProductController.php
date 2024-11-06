<?php

namespace App\Http\Controllers;

use App\Http\Actions\Product\ListProductsAction;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsAction $listProductsAction)
    {
        $products = $listProductsAction->execute($request);

        return ProductResource::collection($products);
    }
}

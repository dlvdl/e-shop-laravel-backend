<?php
namespace App\Http\Controllers\Shop;

use App\Http\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class CustomerProductController extends Controller
{
    public function index()
    {
        $products = Product::getProducts(Product::query());
        return ProductResource::collection($products);
    }


    public function show($slug)
    {
        $product = Product::getBySlug($slug);
        return new ProductResource($product);
    }
}

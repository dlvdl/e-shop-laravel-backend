<?php

namespace App\Http\Controllers;

use App\Models\Product;
use CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        list($products, $cartItems) = CartService::getProductsAndCartItems();
        $totalSum = CartService::getTotalSum();

        return response(compact('products', 'cartItems', 'totalSum'));
    }

    public function addToCart(Request $request, Product $product)
    {
        return response(CartService::addToCart($request, $product));
    }

    public function removeFromCart(Request $request, Product $product)
    {
        return response(CartService::removeFromCart($request, $product));
    }

    public function updateQuantityInCart(Request $request, Product $product)
    {
        return CartService::updateQuantityInCart($request, $product);
    }
}

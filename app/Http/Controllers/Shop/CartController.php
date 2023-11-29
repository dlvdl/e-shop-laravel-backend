<?php

namespace App\Http\Controllers\Shop;


use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        list($products, $cartItems) = CartService::getProductsAndCartItems();
        $totalSum = CartService::getTotalSum($products, $cartItems);

        return response(compact('products', 'cartItems', 'totalSum'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $cartItems = CartService::addToCart($request, $product);
        return response(["count" => CartService::getCartItemsCount($cartItems)])->cookie("cart_items", json_encode($cartItems), 60 * 24 * 30);
    }

    public function removeFromCart(Request $request, Product $product)
    {
        $cartItems = CartService::removeFromCart($request, $product);
        return response(["count" => CartService::getCartItemsCount($cartItems)])->cookie("cart_items", json_encode($cartItems), 60 * 24 * 30);
    }

    public function updateQuantityInCart(Request $request, Product $product)
    {
        $cartItems = CartService::updateQuantityInCart($request, $product);
        return response(["count" => CartService::getCartItemsCount($cartItems)])->cookie("cart_items", json_encode($cartItems), 60 * 24 * 30);
    }
}

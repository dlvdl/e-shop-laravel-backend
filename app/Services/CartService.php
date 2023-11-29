<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;


class CartService
{
    public static function getCartItemsCount($cartItems = null)
    {
        $request = \request();
        $user = $request->user();

        if ($user) {
            return CartItem::where('user_id', $user->id)->sum('quantity');
        }


        return ($cartItems !== null) ? array_reduce($cartItems, fn($carry, $item) => $carry + $item['quantity'], 0) : 0;
    }

    public static function getCartItems()
    {
        $request = \request();
        $user = $request->user();

        if ($user) {
            return CartItem::where('user_id', $user->id)->get()->map(
                fn($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity]
            );
        }

        return self::getCookieCartItems();
    }

    public static function getCartItem(User $user, Product $product)
    {
        return CartItem::where(['user_id' => $user->id, 'product_id' => $product->id])->first();
    }

    public static function getCookieCartItems()
    {
        $request = \request();
        return json_decode($request->cookie('cart_items', '[]'), true);
    }

    public static function getCountFromItems($cartItems)
    {
        return array_reduce(
            $cartItems,
            fn($carry, $item) => $carry + $item['quantity'],
            0
        );
    }

    public static function moveCartItemsIntoDB()
    {
        $request = \request();
        $cartItems = self::getCookieCartItems();
        $dbCartItems = CartItem::where(['user_id' => $request->user()->id])->get()->keyBy('product_id');
        $newCartItems = [];

        foreach ($cartItems as $cartItem) {
            if (isset($dbCartItems[$cartItem['product_id']])) {
                continue;
            }
            $newCartItems[] = [
                'user_id' => $request->user()->id,
                'product_id' => $cartItem['product_id'],
                'quantity' => $cartItem['quantity'],
            ];
        }

        if (!empty($newCartItems)) {
            CartItem::insert($newCartItems);
        }
    }

    public static function getProductsAndCartItems(): array|\Illuminate\Database\Eloquent\Collection
    {
        $cartItems = self::getCartItems();
        // Get all keys of products
        $ids = Arr::pluck($cartItems, 'product_id');
        $products = Product::query()->whereIn('id', $ids)->get();
        // Keys cartItems by product_id
        $cartItems = Arr::keyBy($cartItems, 'product_id');

        return [$products, $cartItems];
    }

    public static function getTotalSum(Collection $products, array $cartItems): int
    {
        $result = 0;
        foreach ($products as $product) {
            $result += $product->price * $cartItems[$product->id]['quantity'];
        }

        return $result;
    }

    public static function addToCart(Request $request, Product $product)
    {
        $quantity = $request->post('quantity', 1);
        $user = $request->user();

        if ($user) {
            $cartItem = self::getCartItem($user, $product);
            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->update();
            } else {
                $data = [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ];
                CartItem::create($data);
            }

            return ['count' => self::getCartItemsCount()];
        }

        $cartItems = json_decode($request->cookie('cart_items', '[]'), true);
        $item_key = array_search($product->id, array_column($cartItems, 'product_id'));

        if ($item_key !== false) {
            $cartItems[$item_key]['quantity'] += $quantity;
        } else {
            $cartItems[] = [
                'user_id' => null,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price
            ];
        }

        // Cookie::queue('cart_items', json_encode($cartItems), 60 * 24 * 30);
        return $cartItems;
    }

    public static function removeFromCart(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user) {
            $cartItem = self::getCartItem($user, $product);
            if ($cartItem) {
                $cartItem->delete();
            }
            return [
                'count' => self::getCartItemsCount(),
            ];
        }

        $cartItems = json_decode($request->cookie('cart_items', '[]'), true);
        //TOdo fix only one product can be remove.
        $cartItems = array_filter($cartItems, function ($item) use ($product) {
            return $item['product_id'] !== $product->id;
        });

        return $cartItems;
    }

    public static function updateQuantityInCart(Request $request, Product $product)
    {
        $quantity = $request->post('quantity');
        $user = $request->user();

        if ($user) {
            self::getCartItem($user, $product)->update(['quantity' => $quantity]);
            return ['count' => self::getCartItemsCount()];
        }

        $cartItems = json_decode($request->cookie('cart_items', '[]'), true);
        $item_key = array_search($product->id, array_column($cartItems, 'product_id'));
        $cartItems[$item_key]['quantity'] = $quantity;
        return $cartItems;
    }
}
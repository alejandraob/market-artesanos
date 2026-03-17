<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function getCart(Request $request)
    {
        $user_id = auth('sanctum')->id();
        $session_id = $request->header('X-Session-Id');

        if (!$user_id && !$session_id) {
            $session_id = Str::uuid()->toString();
        }

        return $user_id 
            ? Cart::firstOrCreate(['user_id' => $user_id], ['session_id' => $session_id])
            : Cart::firstOrCreate(['session_id' => $session_id]);
    }

    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $items = $cart->items()->with(['product.artisan.user'])->get()
                      ->filter(fn($i) => $i->product !== null)->values();

        return response()->json(['session_id' => $cart->session_id, 'items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:1']);
        $cart = $this->getCart($request);
        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $request->quantity]);
            $item = $existing;
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json($item->load('product.artisan.user'), 201);
    }

    public function update(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart = $this->getCart($request);
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);
        return response()->json($item);
    }

    public function destroy(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        CartItem::where('cart_id', $cart->id)->findOrFail($itemId)->delete();
        return response()->json(null, 204);
    }
}

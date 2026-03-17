<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Services\CorreoArgentinoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with('items.product');
        if ($user->role === 'cliente') {
            $query->where('user_id', $user->id);
        }
        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function checkout(Request $request, CorreoArgentinoService $caService)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Carrito vacío'], 400);
        }

        return DB::transaction(function() use ($user, $cart, $caService, $request) {
            $total = 0;
            $items = $cart->items()->with('product')->get();

            foreach ($items as $item) {
                $total += $item->quantity * $item->product->price;
            }

            $shippingCost = $request->input('shipping_cost', 0);
            $total += $shippingCost;

            $order = Order::create([
                'user_id' => $user->id, 
                'total' => $total, 
                'status' => 'pending',
                'shipping_cost' => $shippingCost
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price
                ]);
            }

            $caOrder = $caService->createOrder([
                'extOrderId' => $order->id,
                'orderNumber' => 'MARKET-' . $order->id,
                'recipient' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '00000000',
                ],
            ]);

            if ($caOrder && isset($caOrder['orderNumber'])) {
                $order->update(['shipping_tracking' => $caOrder['orderNumber']]);
            }

            $cart->items()->delete();
            return response()->json($order->load('items.product'), 201);
        });
    }

    public function getShippingRates(Request $request, CorreoArgentinoService $caService)
    {
        $request->validate(['postal_code' => 'required']);
        
        $dimensions = [
            'weight' => 1.0,
            'height' => 10,
            'length' => 10,
            'width' => 10,
        ];

        $rates = $caService->getRates($request->postal_code, $dimensions);
        
        return response()->json($rates);
    }
}

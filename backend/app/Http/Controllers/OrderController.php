<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Mail\OrderConfirmedMail;
use App\Mail\NewOrderForArtisanMail;
use App\Mail\OrderStatusChangedMail;
use App\Services\CorreoArgentinoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with('items.product');

        // Por defecto, solo muestra las ordenes del usuario logueado
        // El dashboard admin usa ?scope=all para ver todas
        if ($request->query('scope') === 'all' && in_array($user->role, ['admin', 'presidente'])) {
            // Sin filtro, devuelve todas
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function checkout(Request $request, CorreoArgentinoService $caService)
    {
        if (!$request->user()->email_verified_at) {
            return response()->json(['message' => 'Debes verificar tu email antes de realizar una compra.'], 403);
        }

        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_province' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_phone' => 'required|string|max:20',
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart || $cart->items->count() === 0) {
            return response()->json(['message' => 'Carrito vacío'], 400);
        }

        return DB::transaction(function() use ($user, $cart, $caService, $request) {
            $items = $cart->items()->with('product.artisan.user')->get();

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item->quantity * $item->product->price;
            }

            $shippingCost = $request->input('shipping_cost', 0);

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $subtotal + $shippingCost,
                'status' => 'pending',
                'shipping_cost' => $shippingCost,
                'shipping_name' => $request->input('shipping_name'),
                'shipping_address' => $request->input('shipping_address'),
                'shipping_city' => $request->input('shipping_city'),
                'shipping_province' => $request->input('shipping_province'),
                'shipping_postal_code' => $request->input('shipping_postal_code'),
                'shipping_phone' => $request->input('shipping_phone'),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price
                ]);
            }

            try {
                $caOrder = $caService->createOrder([
                    'extOrderId' => $order->id,
                    'orderNumber' => 'MARKET-' . $order->id,
                    'recipient' => [
                        'name' => $request->input('shipping_name'),
                        'email' => $user->email,
                        'phone' => $request->input('shipping_phone'),
                    ],
                ]);

                if ($caOrder && isset($caOrder['orderNumber'])) {
                    $order->update(['shipping_tracking' => $caOrder['orderNumber']]);
                }
            } catch (\Exception $e) {
                // El envio se gestionara manualmente si falla la API
            }

            $cart->items()->delete();

            $order->load('items.product.artisan.user', 'user');

            // Email al cliente con confirmacion del pedido
            try {
                Mail::to($user->email)->send(new OrderConfirmedMail($order));
            } catch (\Exception $e) {
                // No bloquear el checkout si falla el email
            }

            // Email a cada artesano con los productos que le corresponden
            try {
                $artisanGroups = [];
                foreach ($order->items as $item) {
                    $artisan = $item->product->artisan ?? null;
                    if (!$artisan) continue;
                    $artisanGroups[$artisan->id]['artisan'] = $artisan;
                    $artisanGroups[$artisan->id]['products'][] = [
                        'name' => $item->product->name,
                        'quantity' => $item->quantity,
                    ];
                }

                foreach ($artisanGroups as $group) {
                    $artisan = $group['artisan'];
                    Mail::to($artisan->user->email)->send(
                        new NewOrderForArtisanMail($order, $artisan->user->name, $group['products'])
                    );
                }
            } catch (\Exception $e) {
                // No bloquear el checkout si falla el email
            }

            return response()->json($order, 201);
        });
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,delivered,cancelled',
            'shipping_tracking' => 'nullable|string|max:255',
        ]);

        $order = Order::with('user')->findOrFail($id);
        $oldStatus = $order->status;
        $order->update($request->only(['status', 'shipping_tracking']));

        // Notificar al cliente si el estado cambio
        if ($oldStatus !== $request->status) {
            try {
                Mail::to($order->user->email)->send(
                    new OrderStatusChangedMail($order, $oldStatus, $request->status)
                );
            } catch (\Exception $e) {
                // No bloquear la actualizacion si falla el email
            }
        }

        return response()->json($order->load('items.product'));
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('items.product.artisan.user')->findOrFail($id);

        if ($user->role === 'cliente' && $order->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($order);
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

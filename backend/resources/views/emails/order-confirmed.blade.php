<x-mail::message>
# Pedido #{{ $order->id }} confirmado

Hola **{{ $order->user->name }}**,

Tu pedido fue registrado correctamente. A continuacion te dejamos el detalle:

<x-mail::table>
| Producto | Cant. | Precio |
|:---------|:-----:|-------:|
@foreach($order->items as $item)
| {{ $item->product->name ?? 'Producto' }} | {{ $item->quantity }} | ${{ number_format($item->unit_price * $item->quantity, 2) }} |
@endforeach
| **Envio** | | ${{ number_format($order->shipping_cost, 2) }} |
| **Total** | | **${{ number_format($order->total, 2) }}** |
</x-mail::table>

## Direccion de envio

{{ $order->shipping_name }}<br>
{{ $order->shipping_address }}<br>
{{ $order->shipping_city }}, {{ $order->shipping_province }} - CP {{ $order->shipping_postal_code }}<br>
Tel: {{ $order->shipping_phone }}

## Contacta al artesano

Los productos son elaborados a pedido. Comunicate con el artesano para coordinar los detalles (color, material, medidas, personalizacion):

@php
$artisans = collect();
foreach ($order->items as $item) {
    $a = $item->product->artisan ?? null;
    if ($a && !$artisans->contains('id', $a->id)) {
        $artisans->push($a);
    }
}
@endphp

@foreach($artisans as $artisan)
**{{ $artisan->user->name }}** ({{ $artisan->specialty }})<br>
@if($artisan->user->phone)Telefono: {{ $artisan->user->phone }}<br>@endif
Email: {{ $artisan->user->email }}<br>
@if($artisan->location)Ubicacion: {{ $artisan->location }}<br>@endif

@endforeach

## Tiempo estimado de entrega

@if($order->items->contains(fn($i) => $i->quantity > 5))
Tu pedido incluye productos con mas de 5 unidades. El tiempo de elaboracion puede superar los 20 dias habiles. El artesano te contactara para coordinar el plazo.
@else
El tiempo estimado de elaboracion y envio es de **15 a 20 dias habiles**.
@endif

Gracias por tu compra,<br>
{{ config('mail.from.name') }}
</x-mail::message>

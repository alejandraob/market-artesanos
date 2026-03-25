<x-mail::message>
@php
$labels = [
    'pending' => 'Pendiente',
    'paid' => 'Pagado',
    'shipped' => 'Enviado',
    'delivered' => 'Entregado',
    'cancelled' => 'Cancelado',
];
@endphp

# Tu pedido #{{ $order->id }} fue actualizado

Hola **{{ $order->user->name }}**,

El estado de tu pedido cambio de **{{ $labels[$oldStatus] ?? $oldStatus }}** a **{{ $labels[$newStatus] ?? $newStatus }}**.

@if($newStatus === 'paid')
Tu pago fue confirmado. El artesano comenzara a elaborar tu pedido.
@elseif($newStatus === 'shipped')
Tu pedido fue enviado.
@if($order->shipping_tracking)
Numero de seguimiento: **{{ $order->shipping_tracking }}**
@endif
@elseif($newStatus === 'delivered')
Tu pedido fue entregado. Esperamos que disfrutes tus productos artesanales.
@elseif($newStatus === 'cancelled')
Tu pedido fue cancelado. Si tenes alguna consulta, no dudes en contactarnos.
@endif

**Total del pedido:** ${{ number_format($order->total, 2) }}

Saludos,<br>
{{ config('mail.from.name') }}
</x-mail::message>

<x-mail::message>
# Nuevo pedido recibido

Hola **{{ $artisanName }}**,

Se registro un nuevo pedido que incluye productos tuyos. A continuacion el detalle:

**Pedido #{{ $order->id }}** - {{ $order->created_at->format('d/m/Y H:i') }}

<x-mail::table>
| Producto | Cantidad |
|:---------|:--------:|
@foreach($products as $product)
| {{ $product['name'] }} | {{ $product['quantity'] }} |
@endforeach
</x-mail::table>

## Datos del cliente

**{{ $order->user->name }}**<br>
Email: {{ $order->user->email }}<br>
@if($order->user->phone)Telefono: {{ $order->user->phone }}<br>@endif

## Direccion de envio

{{ $order->shipping_name }}<br>
{{ $order->shipping_address }}<br>
{{ $order->shipping_city }}, {{ $order->shipping_province }} - CP {{ $order->shipping_postal_code }}

@if(collect($products)->sum('quantity') > 5)
**Nota:** Este pedido incluye productos con mas de 5 unidades. Coordina con el cliente el plazo de entrega.
@endif

El cliente se comunicara contigo para coordinar los detalles del producto (color, material, medidas, personalizacion).

Saludos,<br>
{{ config('mail.from.name') }}
</x-mail::message>

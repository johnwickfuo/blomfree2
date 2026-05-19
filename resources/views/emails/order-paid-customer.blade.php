@component('mail::message')
# Payment received

Hi {{ $order->customer_name }},

We've confirmed payment for order **{{ $order->reference }}**. Your items are now being prepared.

@component('mail::table')
| Item | Qty | Subtotal |
| :--- | :-: | -------: |
@foreach ($order->items as $item)
| {{ $item->item_name }}{{ $item->item_label ? ' ('.$item->item_label.')' : '' }} | {{ $item->quantity }} | ₦{{ number_format((float) $item->subtotal, 2) }} |
@endforeach
@endcomponent

@component('mail::panel')
**Total paid:** ₦{{ number_format((float) $order->total, 2) }}
@endcomponent

@if ($order->hasAnimals())
@component('mail::panel')
**Live animal delivery**

Your order contains a live animal. Live animals require special handling and cannot ship via regular courier — our team will contact you within 24 hours to arrange delivery logistics.
@endcomponent
@elseif ($order->delivery_method === 'delivery')
We'll let you know as soon as your order ships.
@else
You'll receive pickup details shortly.
@endif

@component('mail::button', ['url' => $statusUrl])
View Your Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Thanks for your order

Hi {{ $order->customer_name }},

We've received your order **{{ $order->reference }}** and are waiting on payment confirmation. Once payment is confirmed we'll start preparing your items.

@component('mail::table')
| Item | Qty | Subtotal |
| :--- | :-: | -------: |
@foreach ($order->items as $item)
| {{ $item->item_name }}{{ $item->item_label ? ' ('.$item->item_label.')' : '' }} | {{ $item->quantity }} | ₦{{ number_format((float) $item->subtotal, 2) }} |
@endforeach
@endcomponent

@component('mail::panel')
**Subtotal:** ₦{{ number_format((float) $order->subtotal, 2) }}
**Shipping:** ₦{{ number_format((float) $order->shipping_fee, 2) }}
**Total:** ₦{{ number_format((float) $order->total, 2) }}
@endcomponent

@if ($order->hasAnimals())
@component('mail::panel')
**Live animal delivery**

This order contains a live animal. Live animals cannot ship via regular courier — our team will contact you within 24 hours to arrange delivery logistics.
@endcomponent
@endif

@component('mail::button', ['url' => $statusUrl])
Track Your Order
@endcomponent

Bookmark that link — you can check your order status there any time with no account required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

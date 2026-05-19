@component('mail::message')
# Your order is on the way

Hi {{ $order->customer_name }},

Order **{{ $order->reference }}** has shipped. Here are the details from our team:

@if ($order->tracking_notes)
@component('mail::panel')
{{ $order->tracking_notes }}
@endcomponent
@endif

@component('mail::button', ['url' => $statusUrl])
View Your Order
@endcomponent

If anything looks off, simply reply to this email or reach us on WhatsApp.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# New order — payment pending

A new order has just been placed and is awaiting payment confirmation.

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| Reference | **{{ $order->reference }}** |
| Customer | {{ $order->customer_name }} |
| Email | {{ $order->customer_email }} |
| Phone | {{ $order->customer_phone }} |
| Method | {{ ucfirst($order->delivery_method) }} |
| State | {{ $order->delivery_state }} |
| Gateway | {{ ucfirst($order->payment_gateway) }} |
| Total | ₦{{ number_format((float) $order->total, 2) }} |
| Items | {{ $order->items->sum('quantity') }} |
@endcomponent

@component('mail::button', ['url' => $adminUrl])
Open in Admin
@endcomponent

You'll receive a follow-up email once the customer completes payment.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

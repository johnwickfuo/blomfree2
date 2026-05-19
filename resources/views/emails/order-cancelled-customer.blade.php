@component('mail::message')
# Order cancelled

Hi {{ $order->customer_name }},

Order **{{ $order->reference }}** has been cancelled.

@if ($reason)
@component('mail::panel')
{{ $reason }}
@endcomponent
@endif

If a payment was charged, our team will reach out about the refund. You're welcome to place a new order at any time.

@component('mail::button', ['url' => $landsUrl])
Browse BLOMFREE
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

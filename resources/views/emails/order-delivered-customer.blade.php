@component('mail::message')
# Order delivered

Hi {{ $order->customer_name }},

We're glad to confirm that order **{{ $order->reference }}** has been delivered.

If everything arrived as expected, we'd love to hear from you. If anything is wrong, reply to this email or message us on WhatsApp and we'll make it right.

@component('mail::button', ['url' => $statusUrl])
View Your Order
@endcomponent

Thanks for choosing BLOMFREE,<br>
{{ config('app.name') }}
@endcomponent

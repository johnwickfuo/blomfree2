@component('mail::message')
# Commission earned

Hi {{ $affiliate->user->name }},

A buyer just placed and paid for an order using your code **{{ $affiliate->code }}**.

@component('mail::panel')
**Order:** {{ $order->reference }}<br>
**Commission accrued:** ₦{{ number_format($totalAccrued, 2) }}

This commission starts as **pending**. It moves to your **available** balance once the order is delivered (or after the fallback hold period if delivery isn't confirmed).
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
View Your Commissions
@endcomponent

Keep sharing,<br>
{{ config('app.name') }}
@endcomponent

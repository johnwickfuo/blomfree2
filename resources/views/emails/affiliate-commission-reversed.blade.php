@component('mail::message')
# Commission reversed

Hi {{ $affiliate->user->name }},

Order **{{ $order->reference }}** was cancelled or refunded, so the related commission has been reversed.

@component('mail::panel')
**Amount reversed:** ₦{{ number_format($totalReversed, 2) }}
@endcomponent

This only affects this single order — your other commissions and balance are unchanged.

If you have questions, reply to this email and our team will help.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

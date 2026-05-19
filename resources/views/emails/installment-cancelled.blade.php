@component('mail::message')
# Plan cancelled

Hi {{ $plan->user->name }},

You've cancelled installment plan **{{ $plan->reference }}** for **{{ $plan->installable_label }}**.

@component('mail::panel')
**Paid to date:** ₦{{ number_format((float) $plan->amount_paid, 2) }}<br>
**Refund due (after {{ $plan->forfeiture_percentage }}% forfeiture):** ₦{{ number_format((float) $plan->amount_paid * (100 - $plan->forfeiture_percentage) / 100, 2) }}
@endcomponent

We'll process your refund within 7–14 business days. Please make sure your bank details on file are correct.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

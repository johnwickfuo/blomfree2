@component('mail::message')
# Your installment has defaulted

Hi {{ $plan->user->name }},

Your installment plan **{{ $plan->reference }}** for **{{ $plan->installable_label }}** has defaulted because the deadline passed without full payment.

@component('mail::panel')
**Paid to date:** ₦{{ number_format((float) $plan->amount_paid, 2) }}<br>
**Forfeiture ({{ $plan->forfeiture_percentage }}%):** ₦{{ number_format((float) $plan->amount_paid * $plan->forfeiture_percentage / 100, 2) }}<br>
**Refund due:** ₦{{ number_format((float) $plan->amount_paid * (100 - $plan->forfeiture_percentage) / 100, 2) }}
@endcomponent

@if ($plan->subsidiary === 'lands')
For land plans, refunds are processed once the plot is resold, or after the standard waiting period. Please make sure your bank details on file are correct.
@else
Your refund record is being prepared. We'll process the bank transfer within 7–14 business days. Please verify your bank details on file are correct.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

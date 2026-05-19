@component('mail::message')
# Your plan is approved

Hi {{ $plan->user->name }},

Great news — your installment plan for **{{ $plan->installable_label }}** has been approved.

@component('mail::panel')
**Reference:** {{ $plan->reference }}<br>
**Total:** ₦{{ number_format((float) $plan->total_amount, 2) }}<br>
**Down payment to activate:** ₦{{ number_format((float) $plan->minimum_down_payment_amount, 2) }}
@endcomponent

Make your down payment to lock in your plot.

@component('mail::button', ['url' => $dashboardUrl])
Make Down Payment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

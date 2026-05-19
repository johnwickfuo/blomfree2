@component('mail::message')
# Payment received

Hi {{ $plan->user->name }},

We've received your payment of **₦{{ number_format((float) $payment->amount, 2) }}** toward installment **{{ $plan->reference }}**.

@component('mail::panel')
**Paid to date:** ₦{{ number_format((float) $plan->amount_paid, 2) }} of ₦{{ number_format((float) $plan->total_amount, 2) }}<br>
**Remaining:** ₦{{ number_format($plan->remainingAmount(), 2) }}<br>
**Payment reference:** {{ $payment->reference }}
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
View Your Plan
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

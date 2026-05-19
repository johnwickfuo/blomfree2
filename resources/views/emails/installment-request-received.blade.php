@component('mail::message')
# Request received

Hi {{ $plan->user->name }},

We've received your installment request for **{{ $plan->installable_label }}**.

@component('mail::panel')
**Reference:** {{ $plan->reference }}<br>
**Total:** ₦{{ number_format((float) $plan->total_amount, 2) }}<br>
**Down payment:** ₦{{ number_format((float) $plan->minimum_down_payment_amount, 2) }} ({{ $plan->minimum_down_payment_percentage }}%)<br>
**Maximum length:** {{ $plan->maximum_length_months }} months
@endcomponent

Our team will review and respond within 24–48 hours. If approved, you'll get an email with a link to make your down payment.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

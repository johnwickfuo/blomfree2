@component('mail::message')
# New installment request

@component('mail::table')
| Field | Value |
| :--- | :--- |
| Reference | {{ $plan->reference }} |
| Customer | {{ $plan->user->name }} ({{ $plan->user->email }}) |
| Item | {{ $plan->installable_label }} |
| Total | ₦{{ number_format((float) $plan->total_amount, 2) }} |
| Down payment | ₦{{ number_format((float) $plan->minimum_down_payment_amount, 2) }} ({{ $plan->minimum_down_payment_percentage }}%) |
| Length | {{ $plan->maximum_length_months }} months |
@endcomponent

@component('mail::button', ['url' => $adminUrl])
Review in Admin
@endcomponent
@endcomponent

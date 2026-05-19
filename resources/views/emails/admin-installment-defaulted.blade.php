@component('mail::message')
# Installment plan defaulted

Plan **{{ $plan->reference }}** ({{ $plan->installable_label }}) has defaulted.

**Customer:** {{ $plan->user->name }} ({{ $plan->user->email }})<br>
**Paid to date:** ₦{{ number_format((float) $plan->amount_paid, 2) }}<br>
**Subsidiary:** {{ $plan->subsidiary }}

@if ($plan->subsidiary === 'lands')
The land has been returned to available listings. After the resale waiting period, the customer's refund will be auto-triggered (or you can override sooner from the plan).
@else
A refund record has been auto-created and is awaiting your processing.
@endif

@component('mail::button', ['url' => $adminUrl])
Open Plan
@endcomponent
@endcomponent

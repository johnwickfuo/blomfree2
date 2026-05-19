@component('mail::message')
# Installment plan approaching deadline

Plan **{{ $plan->reference }}** ({{ $plan->installable_label }}) has **{{ $daysLeft }} days** left before deadline.

**Customer:** {{ $plan->user->name }} ({{ $plan->user->email }})<br>
**Outstanding:** ₦{{ number_format($plan->remainingAmount(), 2) }}

Consider reaching out personally.

@component('mail::button', ['url' => $adminUrl])
Open Plan
@endcomponent
@endcomponent

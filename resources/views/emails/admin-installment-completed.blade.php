@component('mail::message')
# Installment plan completed

Plan **{{ $plan->reference }}** for **{{ $plan->installable_label }}** is fully paid.

**Customer:** {{ $plan->user->name }} ({{ $plan->user->email }})<br>
@if ($plan->subsidiary === 'gadgets')
**Action needed:** ship the item or restock check it if marked unavailable.
@else
**Action needed:** transfer documents to customer.
@endif

@component('mail::button', ['url' => $adminUrl])
Open Plan
@endcomponent
@endcomponent

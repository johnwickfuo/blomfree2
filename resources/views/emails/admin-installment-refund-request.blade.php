@component('mail::message')
# Installment refund needs processing

@component('mail::table')
| Field | Value |
| :--- | :--- |
| Reference | {{ $refund->reference }} |
| Customer | {{ $refund->plan->user->name }} ({{ $refund->plan->user->email }}) |
| Plan | {{ $refund->plan->reference }} |
| Paid total | ₦{{ number_format((float) $refund->amount_paid_total, 2) }} |
| Forfeiture | ₦{{ number_format((float) $refund->forfeiture_amount, 2) }} |
| Refund | ₦{{ number_format((float) $refund->refund_amount, 2) }} |
| Trigger | {{ $refund->triggered_by }} |
@endcomponent

@if (! empty($refund->bank_snapshot))
**Bank details:** {{ $refund->bank_snapshot['bank_name'] ?? '—' }} — {{ $refund->bank_snapshot['bank_account_number'] ?? '—' }}
@else
**No bank details on file** — customer has been notified to set them before refund can be processed.
@endif

@component('mail::button', ['url' => $adminUrl])
Process Refund
@endcomponent
@endcomponent

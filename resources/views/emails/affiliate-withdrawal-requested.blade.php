@component('mail::message')
# New withdrawal request

@component('mail::table')
| Field | Value |
| :--- | :--- |
| Reference | {{ $withdrawal->reference }} |
| Affiliate | {{ $withdrawal->affiliate->code }} ({{ $withdrawal->affiliate->user->email }}) |
| Amount | ₦{{ number_format((float) $withdrawal->amount, 2) }} |
| Fee | ₦{{ number_format((float) $withdrawal->fee, 2) }} |
| Net to pay | ₦{{ number_format((float) $withdrawal->net_amount, 2) }} |
@endcomponent

**Bank details (snapshotted at request time):**

- {{ $withdrawal->bank_snapshot['bank_name'] ?? '—' }}
- Account: {{ $withdrawal->bank_snapshot['bank_account_number'] ?? '—' }}
- Name: {{ $withdrawal->bank_snapshot['bank_account_name'] ?? '—' }}

@component('mail::button', ['url' => $adminUrl])
Review in Admin
@endcomponent
@endcomponent

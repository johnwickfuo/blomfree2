@component('mail::message')
# Affiliate balance went negative

A commission reversal pushed an affiliate balance below zero — this usually means the affiliate already withdrew funds that have now been reversed.

@component('mail::table')
| Field | Value |
| :--- | :--- |
| Affiliate | {{ $affiliate->code }} ({{ $affiliate->user->email }}) |
| Order | {{ $order->reference }} |
| Amount reversed | ₦{{ number_format($totalReversed, 2) }} |
| Pending balance | ₦{{ number_format((float) $affiliate->fresh()->pending_balance, 2) }} |
| Available balance | ₦{{ number_format((float) $affiliate->fresh()->available_balance, 2) }} |
@endcomponent

**Action:** Review the affiliate's account and decide whether to apply a manual balance adjustment, recover funds, or suspend the account.
@endcomponent

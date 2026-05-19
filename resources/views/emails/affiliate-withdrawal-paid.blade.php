@component('mail::message')
# Withdrawal paid

Hi {{ $withdrawal->affiliate->user->name }},

Your withdrawal **{{ $withdrawal->reference }}** has been paid out.

@component('mail::panel')
**Amount paid:** ₦{{ number_format((float) $withdrawal->net_amount, 2) }}<br>
**Sent to:** {{ $withdrawal->bank_snapshot['bank_name'] ?? '—' }} — {{ $withdrawal->bank_snapshot['bank_account_number'] ?? '—' }}<br>
**Payment reference:** {{ $withdrawal->payment_reference }}
@endcomponent

If you don't see the deposit in your bank account within 24 hours, reply to this email with your payment reference.

@component('mail::button', ['url' => $dashboardUrl])
View Withdrawals
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

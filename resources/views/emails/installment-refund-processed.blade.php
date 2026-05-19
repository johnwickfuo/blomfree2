@component('mail::message')
# Refund processed

Hi {{ $refund->plan->user->name }},

Your refund **{{ $refund->reference }}** has been processed.

@component('mail::panel')
**Refund amount:** ₦{{ number_format((float) $refund->refund_amount, 2) }}<br>
**Sent to:** {{ $refund->bank_snapshot['bank_name'] ?? '—' }} — {{ $refund->bank_snapshot['bank_account_number'] ?? '—' }}<br>
**Payment reference:** {{ $refund->payment_reference }}
@endcomponent

If you don't see the deposit within 24 hours, reply to this email with the payment reference.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Withdrawal could not be processed

Hi {{ $withdrawal->affiliate->user->name }},

Your withdrawal **{{ $withdrawal->reference }}** for ₦{{ number_format((float) $withdrawal->amount, 2) }} was not processed.

@component('mail::panel')
**Reason:** {{ $reason }}
@endcomponent

The full amount has been returned to your available balance. You can update your details and try again from your dashboard.

@component('mail::button', ['url' => $dashboardUrl])
Go to Withdrawals
@endcomponent

If you think this is a mistake, reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

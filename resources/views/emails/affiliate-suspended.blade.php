@component('mail::message')
# Your affiliate account has been suspended

Hi {{ $affiliate->user->name }},

Your affiliate account ({{ $affiliate->code }}) has been suspended.

@if ($reason)
@component('mail::panel')
**Reason:** {{ $reason }}
@endcomponent
@endif

While suspended:

- Your code will no longer work at checkout.
- Existing commissions stay on your account, but no new ones can accrue.
- Withdrawals are paused until the suspension is lifted.

If you'd like to dispute this, reply to this email and our team will review.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

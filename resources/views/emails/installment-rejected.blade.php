@component('mail::message')
# About your installment request

Hi {{ $plan->user->name }},

Unfortunately, your installment request for **{{ $plan->installable_label }}** ({{ $plan->reference }}) was not approved.

@component('mail::panel')
**Reason:** {{ $reason }}
@endcomponent

If you'd like to discuss, reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

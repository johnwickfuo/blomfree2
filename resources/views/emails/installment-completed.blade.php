@component('mail::message')
# Plan fully paid!

Hi {{ $plan->user->name }},

Congratulations — you've completed your installment for **{{ $plan->installable_label }}**.

@component('mail::panel')
**Reference:** {{ $plan->reference }}<br>
**Total paid:** ₦{{ number_format((float) $plan->amount_paid, 2) }}
@endcomponent

@if ($plan->subsidiary === 'lands')
Our team will reach out shortly to arrange the document transfer (deed of assignment, survey plan, etc.).
@else
Our team will package and ship your item. You'll receive a follow-up email when it's on the way.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

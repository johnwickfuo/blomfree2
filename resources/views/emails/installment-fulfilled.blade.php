@component('mail::message')
# {{ $plan->subsidiary === 'lands' ? 'Documents transferred' : 'Item shipped' }}

Hi {{ $plan->user->name }},

Your installment **{{ $plan->reference }}** for **{{ $plan->installable_label }}** has been fulfilled.

@if ($plan->subsidiary === 'lands')
The land documents have been transferred to you. If you have any questions about the paperwork, reply to this email.
@else
Your item is on its way. Look out for delivery within the next few days.
@endif

Thanks for choosing BLOMFREE,<br>
{{ config('app.name') }}
@endcomponent

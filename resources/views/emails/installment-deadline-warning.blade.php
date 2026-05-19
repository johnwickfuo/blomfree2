@component('mail::message')
# {{ $daysLeft }} days left on your installment

Hi {{ $plan->user->name }},

Your installment plan **{{ $plan->reference }}** ({{ $plan->installable_label }}) has **{{ $daysLeft }} days** left before the deadline.

@component('mail::panel')
**Outstanding balance:** ₦{{ number_format($plan->remainingAmount(), 2) }}<br>
**Deadline:** {{ $plan->deadline?->format('d M Y') }}<br>
**Reminder:** if not completed by the deadline, 10% of what you've paid is forfeited and 90% is refunded.
@endcomponent

@component('mail::button', ['url' => $dashboardUrl])
Complete Your Plan
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

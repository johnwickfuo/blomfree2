@component('mail::message')
# Your plan is active

Hi {{ $plan->user->name }},

Your down payment cleared. Your installment plan **{{ $plan->reference }}** for **{{ $plan->installable_label }}** is now active.

@component('mail::panel')
**Deadline:** {{ $plan->deadline?->format('d M Y') }}<br>
**Remaining balance:** ₦{{ number_format($plan->remainingAmount(), 2) }}
@endcomponent

Pay any amount, any time, until the deadline. Early completion is welcome and there's no penalty.

@component('mail::button', ['url' => $dashboardUrl])
View Your Plan
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

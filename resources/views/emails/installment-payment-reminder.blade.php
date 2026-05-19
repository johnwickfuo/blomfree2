@component('mail::message')
# Payment due soon

Hi {{ $plan->user->name }},

This is a friendly reminder that your next suggested payment toward **{{ $plan->plan->installable_label ?? $plan->installable_label }}** is due on **{{ $dueDate }}**.

@component('mail::panel')
**Suggested amount:** ₦{{ number_format($suggestedAmount, 2) }}<br>
**Plan reference:** {{ $plan->reference }}
@endcomponent

Suggested dates are guidelines — only the overall deadline matters. Pay anytime before {{ $plan->deadline?->format('d M Y') }}.

@component('mail::button', ['url' => $dashboardUrl])
Make a Payment
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

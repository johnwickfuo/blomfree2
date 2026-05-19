@component('mail::message')
# Your affiliate account is active again

Hi {{ $affiliate->user->name }},

Good news — your affiliate account ({{ $affiliate->code }}) has been reactivated.

You can start sharing your code again right away. Any commissions on your account are intact and will continue to follow the standard hold and release schedule.

@component('mail::button', ['url' => $dashboardUrl])
Go to Dashboard
@endcomponent

Welcome back,<br>
{{ config('app.name') }}
@endcomponent

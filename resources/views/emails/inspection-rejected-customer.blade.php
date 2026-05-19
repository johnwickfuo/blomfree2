@component('mail::message')
# Update on your inspection request

Hi {{ $inspection->customer_name }},

Thank you for your interest in **{{ $inspection->inspectable?->title ?? 'a BLOMFREE property' }}**. Unfortunately, we are unable to confirm your inspection request (reference **{{ $inspection->reference }}**) at this time.

@if ($inspection->admin_notes)
@component('mail::panel')
{{ $inspection->admin_notes }}
@endcomponent
@endif

This does not affect your ability to book again. You are welcome to choose another date, or explore other available plots from our Estates portfolio.

@component('mail::button', ['url' => $landsUrl])
Browse Available Lands
@endcomponent

If you have any questions, simply reply to this email or reach us on WhatsApp.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

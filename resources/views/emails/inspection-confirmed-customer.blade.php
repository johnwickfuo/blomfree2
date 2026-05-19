@component('mail::message')
# We received your inspection request

Hi {{ $inspection->customer_name }},

Thank you for your interest in **{{ $inspection->inspectable?->title ?? 'a BLOMFREE property' }}**. We have received your site inspection request and our team will confirm it within **24 hours**.

@component('mail::panel')
Your reference is **{{ $inspection->reference }}**. Please keep it safe — you can use it to check your booking status at any time.
@endcomponent

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| Preferred date | {{ $inspection->preferred_date?->format('l, j M Y') }} |
| Time slot | {{ $inspection->preferred_time_slot }} |
| Alternate date | {{ $inspection->alternate_date?->format('l, j M Y') ?? '—' }} |
| Party size | {{ $inspection->party_size }} |
@endcomponent

@component('mail::button', ['url' => $statusUrl])
Check Your Booking Status
@endcomponent

Once your inspection is approved, we will email you the meeting address and any instructions you need.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Your inspection is approved

Hi {{ $inspection->customer_name }},

Good news — your site inspection for **{{ $inspection->inspectable?->title ?? 'a BLOMFREE property' }}** has been approved.

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| Reference | **{{ $inspection->reference }}** |
| Date | {{ $inspection->preferred_date?->format('l, j M Y') }} |
| Time slot | {{ $inspection->preferred_time_slot }} |
| Party size | {{ $inspection->party_size }} |
@endcomponent

@component('mail::panel')
**Meeting address**

{{ $inspection->meeting_address }}
@endcomponent

@if ($inspection->meeting_instructions)
**Instructions from our team**

{{ $inspection->meeting_instructions }}
@endif

@component('mail::button', ['url' => $statusUrl])
View Your Booking
@endcomponent

Please arrive on time and bring a valid means of identification. If anything changes, contact us as early as possible.

We look forward to meeting you,<br>
{{ config('app.name') }}
@endcomponent

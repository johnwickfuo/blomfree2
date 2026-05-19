@component('mail::message')
# New Inspection Request

A new site inspection request has just been submitted on the BLOMFREE website.

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| Reference | **{{ $inspection->reference }}** |
| Property | {{ $inspection->inspectable?->title ?? 'Unavailable' }} |
| Customer | {{ $inspection->customer_name }} |
| Email | {{ $inspection->customer_email }} |
| Phone | {{ $inspection->customer_phone }} |
| Preferred date | {{ $inspection->preferred_date?->format('l, j M Y') }} |
| Time slot | {{ $inspection->preferred_time_slot }} |
| Alternate date | {{ $inspection->alternate_date?->format('l, j M Y') ?? '—' }} |
| Party size | {{ $inspection->party_size }} |
| Status | {{ ucfirst($inspection->status) }} |
@endcomponent

@if ($inspection->notes)
**Customer notes:**

{{ $inspection->notes }}
@endif

@component('mail::button', ['url' => $adminUrl])
Review in Admin
@endcomponent

Approve or reject this request from the admin panel — the customer is notified automatically.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

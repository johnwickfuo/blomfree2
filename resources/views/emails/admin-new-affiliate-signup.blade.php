@component('mail::message')
# New affiliate signup

A new affiliate has joined the program.

@component('mail::table')
| Field | Value |
| :--- | :--- |
| Name | {{ $affiliate->user->name }} |
| Email | {{ $affiliate->user->email }} |
| Phone | {{ $affiliate->user->phone ?? '—' }} |
| WhatsApp | {{ $affiliate->whatsapp_number ?? '—' }} |
| Code | {{ $affiliate->code }} |
| Status | {{ ucfirst($affiliate->status) }} |
@endcomponent

@if (! empty($affiliate->social_handles))
**Socials:**
@foreach ($affiliate->social_handles as $platform => $handle)
- {{ ucfirst($platform) }}: {{ $handle }}
@endforeach
@endif

@component('mail::button', ['url' => $adminUrl])
View in Admin
@endcomponent
@endcomponent

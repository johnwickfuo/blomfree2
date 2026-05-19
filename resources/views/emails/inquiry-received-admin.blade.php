@component('mail::message')
# New inquiry — {{ $subjectLabel }}

A new message has come in through the contact form.

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| From | **{{ $inquiry->name }}** |
| Email | {{ $inquiry->email }} |
| Phone | {{ $inquiry->phone }} |
| Subject | {{ $subjectLabel }} |
| Came from | {{ $inquiry->related_url ?? '—' }} |
@endcomponent

**Message:**

> {{ $inquiry->message }}

@component('mail::button', ['url' => $adminUrl])
Open in Admin
@endcomponent

Reply directly to {{ $inquiry->email }} or use the "Quick respond" action on the inquiry.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

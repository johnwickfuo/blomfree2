@component('mail::message')
# Welcome to the BLOMFREE Affiliate Program

Hi {{ $affiliate->user->name }},

Your application is approved and your affiliate code is ready to go.

@component('mail::panel')
**Your unique code: {{ $affiliate->code }}**

Share this code with anyone shopping on BLOMFREE. When they enter it at checkout on an eligible product or animal, they get a small discount — and you earn a fixed commission.
@endcomponent

## How it works

1. Share your code on WhatsApp, Instagram, Twitter — wherever your audience is.
2. Your buyer enters the code at checkout. They see the affiliate price.
3. Once the order is paid and delivered, your commission unlocks.
4. Withdraw to your Nigerian bank account whenever your available balance hits the minimum.

@component('mail::button', ['url' => $dashboardUrl])
Open Your Dashboard
@endcomponent

Before you start, please review our [Affiliate Terms]({{ $termsUrl }}). They cover what counts as fair promotion, when commissions can be reversed, and how payouts work.

Welcome aboard,<br>
{{ config('app.name') }}
@endcomponent

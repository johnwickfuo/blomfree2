@component('mail::message')
# URGENT: Live animal order

An order containing one or more live animals has just been placed and needs a fast human response — please contact the customer to arrange delivery logistics.

@component('mail::table')
| Detail | Value |
| :----- | :---- |
| Reference | **{{ $order->reference }}** |
| Customer | {{ $order->customer_name }} |
| Email | {{ $order->customer_email }} |
| Phone | {{ $order->customer_phone }} |
| Delivery state | {{ $order->delivery_state }} |
| Total | ₦{{ number_format((float) $order->total, 2) }} |
@endcomponent

**Animal lines on this order:**

@foreach ($order->items as $item)
@if ($item->orderable_type === \App\Models\Animal::class)
- {{ $item->quantity }} × {{ $item->item_name }}{{ $item->item_label ? ' — '.$item->item_label : '' }}
@endif
@endforeach

@component('mail::button', ['url' => $adminUrl])
Open Order in Admin
@endcomponent

Live animals cannot ship via regular courier — confirm logistics within 24 hours.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

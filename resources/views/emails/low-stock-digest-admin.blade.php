@component('mail::message')
# Low stock digest

The nightly stock check found the following items with **fewer than 5 in stock**. Replenish or restock as needed.

@if ($variants->isNotEmpty())
## Product variants ({{ $variants->count() }})

@component('mail::table')
| Product | Variant | Stock |
| :------ | :------ | ----: |
@foreach ($variants as $variant)
| {{ $variant->product?->name ?? '—' }} | {{ $variant->attributes_label ?: '—' }} | **{{ $variant->stock }}** |
@endforeach
@endcomponent
@endif

@if ($animals->isNotEmpty())
## Pool animals ({{ $animals->count() }})

@component('mail::table')
| Listing | Breed | Stock |
| :------ | :---- | ----: |
@foreach ($animals as $animal)
| {{ $animal->name }} | {{ $animal->breed }} | **{{ $animal->stock }}** |
@endforeach
@endcomponent
@endif

@if ($variants->isEmpty() && $animals->isEmpty())
Everything is comfortably stocked — no action needed.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

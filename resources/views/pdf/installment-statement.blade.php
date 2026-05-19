<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Statement {{ $plan->reference }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
    h1 { color: #f97316; margin-bottom: 4px; }
    .header { border-bottom: 2px solid #f97316; padding-bottom: 12px; margin-bottom: 24px; }
    .meta td { padding: 4px 8px; }
    .meta td:first-child { font-weight: bold; color: #555; }
    table.payments { width: 100%; border-collapse: collapse; margin-top: 16px; }
    table.payments th, table.payments td { border: 1px solid #ddd; padding: 6px; text-align: left; }
    table.payments th { background: #fff4e6; }
    .summary { background: #fff4e6; padding: 12px; border-radius: 8px; margin: 20px 0; }
    .status { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #e5e7eb; font-size: 11px; }
    .footer { margin-top: 40px; font-size: 10px; color: #999; }
</style>
</head>
<body>

<div class="header">
    <h1>BLOMFREE & CO. NIG. LTD</h1>
    <div>Yenagoa, Bayelsa State — admin@blomfree.com</div>
</div>

<h2>Installment Plan Statement</h2>

<table class="meta">
    <tr><td>Plan #</td><td>{{ $plan->reference }}</td></tr>
    <tr><td>Customer</td><td>{{ $plan->user->name }} ({{ $plan->user->email }})</td></tr>
    <tr><td>Item</td><td>{{ $plan->installable_label }}</td></tr>
    <tr><td>Status</td><td><span class="status">{{ str_replace('_', ' ', $plan->status) }}</span></td></tr>
    <tr><td>Requested</td><td>{{ $plan->requested_at?->format('d M Y') }}</td></tr>
    <tr><td>Activated</td><td>{{ $plan->activated_at?->format('d M Y') ?? '—' }}</td></tr>
    <tr><td>Deadline</td><td>{{ $plan->deadline?->format('d M Y') ?? '—' }}</td></tr>
</table>

<div class="summary">
    <strong>Plan total:</strong> NGN {{ number_format((float) $plan->total_amount, 2) }}<br>
    <strong>Paid to date:</strong> NGN {{ number_format((float) $plan->amount_paid, 2) }}
    ({{ number_format($plan->progressPercentage(), 1) }}% complete)<br>
    <strong>Remaining:</strong> NGN {{ number_format($plan->remainingAmount(), 2) }}
</div>

<h3>Payment History</h3>

<table class="payments">
    <thead>
        <tr><th>Date</th><th>Reference</th><th>Method</th><th style="text-align:right;">Amount</th></tr>
    </thead>
    <tbody>
        @forelse ($plan->payments as $p)
            <tr>
                <td>{{ $p->paid_at?->format('d M Y') }}</td>
                <td>{{ $p->reference }}</td>
                <td>{{ ucfirst($p->payment_gateway) }}</td>
                <td style="text-align:right;">NGN {{ number_format((float) $p->amount, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center; color:#999;">No payments yet.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generated {{ now()->format('d M Y H:i') }}.<br>
    Plan agreed under installment terms version {{ $plan->terms_version }}. The 10% forfeiture clause applies to defaulted or cancelled plans.
</div>

</body>
</html>

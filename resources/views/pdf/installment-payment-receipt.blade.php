<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->reference }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
    h1 { color: #f97316; margin-bottom: 4px; }
    .header { border-bottom: 2px solid #f97316; padding-bottom: 12px; margin-bottom: 24px; }
    .meta { margin: 16px 0; }
    .meta td { padding: 4px 8px; }
    .meta td:first-child { font-weight: bold; color: #555; }
    .total { background: #fff4e6; padding: 12px; border-radius: 8px; margin-top: 16px; font-size: 16px; }
    .footer { margin-top: 40px; font-size: 10px; color: #999; }
</style>
</head>
<body>

<div class="header">
    <h1>BLOMFREE & CO. NIG. LTD</h1>
    <div>Yenagoa, Bayelsa State — admin@blomfree.com</div>
</div>

<h2>Installment Payment Receipt</h2>

<table class="meta">
    <tr><td>Receipt #</td><td>{{ $payment->reference }}</td></tr>
    <tr><td>Plan #</td><td>{{ $plan->reference }}</td></tr>
    <tr><td>Customer</td><td>{{ $plan->user->name }} ({{ $plan->user->email }})</td></tr>
    <tr><td>Item</td><td>{{ $plan->installable_label }}</td></tr>
    <tr><td>Paid on</td><td>{{ $payment->paid_at?->format('d M Y H:i') }}</td></tr>
    <tr><td>Method</td><td>{{ ucfirst($payment->payment_gateway) }} — ref {{ $payment->payment_reference }}</td></tr>
</table>

<div class="total">
    Amount paid: <strong>NGN {{ number_format((float) $payment->amount, 2) }}</strong>
</div>

<table class="meta" style="margin-top: 16px;">
    <tr><td>Plan total</td><td>NGN {{ number_format((float) $plan->total_amount, 2) }}</td></tr>
    <tr><td>Paid to date</td><td>NGN {{ number_format((float) $plan->amount_paid, 2) }}</td></tr>
    <tr><td>Remaining</td><td>NGN {{ number_format($plan->remainingAmount(), 2) }}</td></tr>
    <tr><td>Deadline</td><td>{{ $plan->deadline?->format('d M Y') ?? '—' }}</td></tr>
</table>

<div class="footer">
    Generated {{ now()->format('d M Y H:i') }}.<br>
    This receipt is computer-generated and does not require a signature.
</div>

</body>
</html>

<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPlan;
use App\Models\Order;
use App\Services\InstallmentService;
use App\Services\OrderService;
use App\Support\PaymentGateways\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly InstallmentService $installments,
        private readonly PaymentGatewayManager $gateways,
    ) {
    }

    public function paystack(Request $request): Response
    {
        $gateway = $this->gateways->for('paystack');

        if (! $gateway->validateWebhook($request)) {
            Log::warning('Paystack webhook signature invalid.');

            return response('Invalid signature', 401);
        }

        $event = $request->input('event');
        if ($event !== 'charge.success') {
            return response('Ignored', 200);
        }

        $data = $request->input('data', []);
        $reference = (string) data_get($data, 'reference');
        $transactionId = data_get($data, 'id');
        $amountKobo = (int) data_get($data, 'amount', 0);
        $metadata = data_get($data, 'metadata', []);

        if (data_get($metadata, 'payment_type') === 'installment') {
            return $this->finaliseInstallment(
                'paystack',
                $reference,
                $amountKobo / 100,
                (int) data_get($metadata, 'installment_plan_id'),
                $data,
            );
        }

        return $this->finaliseOrder($reference, $transactionId !== null ? (string) $transactionId : null);
    }

    public function flutterwave(Request $request): Response
    {
        $gateway = $this->gateways->for('flutterwave');

        if (! $gateway->validateWebhook($request)) {
            Log::warning('Flutterwave webhook signature invalid.');

            return response('Invalid signature', 401);
        }

        $payload = $request->input('data', []);
        $status = data_get($payload, 'status');

        if ($status !== 'successful') {
            return response('Ignored', 200);
        }

        $reference = (string) data_get($payload, 'tx_ref');
        $transactionId = data_get($payload, 'id');
        $amount = (float) data_get($payload, 'amount', 0);
        $metadata = data_get($payload, 'meta', []);

        if (data_get($metadata, 'payment_type') === 'installment') {
            return $this->finaliseInstallment(
                'flutterwave',
                $reference,
                $amount,
                (int) data_get($metadata, 'installment_plan_id'),
                $payload,
            );
        }

        return $this->finaliseOrder($reference, $transactionId !== null ? (string) $transactionId : null);
    }

    private function finaliseOrder(string $reference, ?string $transactionId): Response
    {
        $order = Order::query()->where('reference', $reference)->first();

        if (! $order) {
            return response('Order not found', 404);
        }

        $this->orders->markPaid($order, $transactionId);

        return response('OK', 200);
    }

    private function finaliseInstallment(string $gateway, string $reference, float $amount, int $planId, array $raw): Response
    {
        $plan = InstallmentPlan::query()->whereKey($planId)->first();
        if (! $plan) {
            return response('Plan not found', 404);
        }

        $this->installments->recordPayment($plan, [
            'amount' => $amount,
            'payment_gateway' => $gateway,
            'payment_reference' => $reference,
            'gateway_response' => $raw,
        ]);

        return response('OK', 200);
    }
}

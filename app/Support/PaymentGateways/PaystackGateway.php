<?php

namespace App\Support\PaymentGateways;

use App\Models\InstallmentPlan;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway
{
    public function name(): string
    {
        return 'paystack';
    }

    /**
     * Initialise a payment with Paystack and return the hosted-checkout URL.
     */
    public function initialize(Order $order, string $callbackUrl): ?string
    {
        $secret = config('services.paystack.secret_key');
        $baseUrl = rtrim(config('services.paystack.payment_url'), '/');

        if (! $secret) {
            Log::error('Paystack secret key is not configured.');

            return null;
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post($baseUrl.'/transaction/initialize', [
                'email' => $order->customer_email,
                'amount' => (int) round((float) $order->total * 100), // kobo
                'currency' => 'NGN',
                'reference' => $order->reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'order_reference' => $order->reference,
                    'customer_name' => $order->customer_name,
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::warning('Paystack init failed', ['order' => $order->reference, 'body' => $response->json()]);

            return null;
        }

        return $response->json('data.authorization_url');
    }

    /**
     * Initialise a payment toward an installment plan. Each call uses a
     * unique tx-reference so the same plan can be paid multiple times.
     */
    public function initializeForInstallment(InstallmentPlan $plan, float $amount, string $callbackUrl): ?array
    {
        $secret = config('services.paystack.secret_key');
        $baseUrl = rtrim(config('services.paystack.payment_url'), '/');

        if (! $secret) {
            Log::error('Paystack secret key is not configured.');
            return null;
        }

        $reference = $plan->reference.'-'.Str::upper(Str::random(6));

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post($baseUrl.'/transaction/initialize', [
                'email' => $plan->user->email,
                'amount' => (int) round($amount * 100),
                'currency' => 'NGN',
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'installment_plan_id' => $plan->id,
                    'installment_plan_reference' => $plan->reference,
                    'user_id' => $plan->user_id,
                    'payment_type' => 'installment',
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::warning('Paystack init failed (installment)', ['plan' => $plan->reference, 'body' => $response->json()]);
            return null;
        }

        return ['reference' => $reference, 'authorization_url' => $response->json('data.authorization_url')];
    }

    /**
     * Verify a transaction with Paystack by our order reference.
     */
    public function verify(string $reference): GatewayResult
    {
        $secret = config('services.paystack.secret_key');
        $baseUrl = rtrim(config('services.paystack.payment_url'), '/');

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get($baseUrl.'/transaction/verify/'.urlencode($reference));

        $data = $response->json('data', []);
        $success = $response->successful()
            && $response->json('status') === true
            && data_get($data, 'status') === 'success';

        return new GatewayResult(
            success: $success,
            transactionId: data_get($data, 'id') ? (string) data_get($data, 'id') : null,
            amount: data_get($data, 'amount') !== null ? ((float) data_get($data, 'amount')) / 100 : null,
            raw: is_array($data) ? $data : [],
        );
    }

    /**
     * Validate a Paystack webhook payload via the SHA-512 HMAC signature.
     */
    public function validateWebhook(Request $request): bool
    {
        $secret = config('services.paystack.secret_key');
        $signature = $request->header('X-Paystack-Signature');

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}

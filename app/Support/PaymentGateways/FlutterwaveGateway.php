<?php

namespace App\Support\PaymentGateways;

use App\Models\InstallmentPlan;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlutterwaveGateway
{
    public function name(): string
    {
        return 'flutterwave';
    }

    /**
     * Initialise a Flutterwave payment and return the hosted-checkout link.
     */
    public function initialize(Order $order, string $callbackUrl): ?string
    {
        $secret = config('services.flutterwave.secret_key');
        $baseUrl = rtrim(config('services.flutterwave.payment_url'), '/');

        if (! $secret) {
            Log::error('Flutterwave secret key is not configured.');

            return null;
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post($baseUrl.'/payments', [
                'tx_ref' => $order->reference,
                'amount' => (float) $order->total,
                'currency' => 'NGN',
                'redirect_url' => $callbackUrl,
                'customer' => [
                    'email' => $order->customer_email,
                    'phonenumber' => $order->customer_phone,
                    'name' => $order->customer_name,
                ],
                'customizations' => [
                    'title' => config('app.name'),
                    'description' => 'Order '.$order->reference,
                ],
                'meta' => [
                    'order_reference' => $order->reference,
                ],
            ]);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::warning('Flutterwave init failed', ['order' => $order->reference, 'body' => $response->json()]);

            return null;
        }

        return $response->json('data.link');
    }

    /**
     * Initialise a payment for an installment plan with Flutterwave.
     */
    public function initializeForInstallment(InstallmentPlan $plan, float $amount, string $callbackUrl): ?array
    {
        $secret = config('services.flutterwave.secret_key');
        $baseUrl = rtrim(config('services.flutterwave.payment_url'), '/');

        if (! $secret) {
            Log::error('Flutterwave secret key is not configured.');
            return null;
        }

        $reference = $plan->reference.'-'.Str::upper(Str::random(6));

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post($baseUrl.'/payments', [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => 'NGN',
                'redirect_url' => $callbackUrl,
                'customer' => [
                    'email' => $plan->user->email,
                    'phonenumber' => $plan->user->phone,
                    'name' => $plan->user->name,
                ],
                'customizations' => [
                    'title' => config('app.name'),
                    'description' => 'Installment '.$plan->reference,
                ],
                'meta' => [
                    'installment_plan_id' => $plan->id,
                    'installment_plan_reference' => $plan->reference,
                    'user_id' => $plan->user_id,
                    'payment_type' => 'installment',
                ],
            ]);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::warning('Flutterwave init failed (installment)', ['plan' => $plan->reference, 'body' => $response->json()]);
            return null;
        }

        return ['reference' => $reference, 'authorization_url' => $response->json('data.link')];
    }

    /**
     * Verify a Flutterwave transaction by our tx_ref.
     */
    public function verify(string $reference): GatewayResult
    {
        $secret = config('services.flutterwave.secret_key');
        $baseUrl = rtrim(config('services.flutterwave.payment_url'), '/');

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get($baseUrl.'/transactions/verify_by_reference', [
                'tx_ref' => $reference,
            ]);

        $data = $response->json('data', []);
        $success = $response->successful()
            && $response->json('status') === 'success'
            && data_get($data, 'status') === 'successful';

        return new GatewayResult(
            success: $success,
            transactionId: data_get($data, 'id') ? (string) data_get($data, 'id') : null,
            amount: data_get($data, 'amount') !== null ? (float) data_get($data, 'amount') : null,
            raw: is_array($data) ? $data : [],
        );
    }

    /**
     * Validate a Flutterwave webhook via the verif-hash header.
     */
    public function validateWebhook(Request $request): bool
    {
        $expected = config('services.flutterwave.secret_hash');
        $header = $request->header('verif-hash');

        if (! $expected || ! $header) {
            return false;
        }

        return hash_equals($expected, $header);
    }
}

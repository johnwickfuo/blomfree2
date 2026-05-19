<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPlan;
use App\Models\Land;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\AffiliateService;
use App\Services\CartService;
use App\Services\InstallmentService;
use App\Support\PaymentGateways\FlutterwaveGateway;
use App\Support\PaymentGateways\PaymentGatewayManager;
use App\Support\PaymentGateways\PaystackGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InstallmentController extends Controller
{
    public function __construct(
        private readonly InstallmentService $installments,
        private readonly AffiliateService $affiliates,
        private readonly CartService $cart,
        private readonly PaymentGatewayManager $gateways,
    ) {
    }

    public function landing(): Response
    {
        return Inertia::render('Installments/Landing', [
            'forfeiturePercentage' => (int) Setting::get('installment_forfeiture_percentage', '10'),
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Installments/Terms', [
            'termsVersion' => (string) Setting::get('installment_terms_version', 'v1.0'),
            'forfeiturePercentage' => (int) Setting::get('installment_forfeiture_percentage', '10'),
            'landResaleWaitDays' => (int) Setting::get('installment_land_resale_wait_days', '90'),
        ]);
    }

    /**
     * Show the terms/preview screen for a specific item.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        [$installable, $error] = $this->resolveInstallable($request);
        if ($error) {
            return redirect('/')->with('error', $error);
        }

        $code = $this->cart->appliedAffiliateCode();
        $pricing = $this->previewPricing($installable, $code);

        return Inertia::render('Installments/Initiate', [
            'installable' => $this->serializeInstallable($installable),
            'pricing' => $pricing,
            'affiliateCode' => $code,
            'isLoggedIn' => Auth::check(),
            'termsVersion' => (string) Setting::get('installment_terms_version', 'v1.0'),
            'forfeiturePercentage' => (int) Setting::get('installment_forfeiture_percentage', '10'),
        ]);
    }

    /**
     * Submit a new installment plan request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'installable_type' => ['required', 'string', 'in:land,product,product_variant'],
            'installable_id' => ['required', 'integer'],
            'agreed_to_terms' => ['accepted'],
            'request_notes' => ['nullable', 'string', 'max:1000'],
            'inspection_status' => ['nullable', 'string', 'max:50'],
            'preferred_contact' => ['nullable', 'string', 'in:email,phone,whatsapp'],
        ]);

        $installable = $this->loadInstallable($data['installable_type'], (int) $data['installable_id']);
        if (! $installable) {
            return back()->withErrors(['installable_id' => 'Item not found.']);
        }
        if (! $installable->installmentAvailableForPurchase()) {
            return back()->withErrors(['installable_id' => 'This item is not available for installment purchase.']);
        }

        // For lands, prevent multiple plans on the same plot.
        if ($installable instanceof Land
            && InstallmentPlan::query()
                ->where('installable_type', Land::class)
                ->where('installable_id', $installable->id)
                ->whereIn('status', ['pending_approval', 'awaiting_down_payment', 'active', 'completed', 'awaiting_fulfillment'])
                ->exists()
        ) {
            return back()->withErrors(['installable_id' => 'This land is already reserved by another customer.']);
        }

        $plan = $this->installments->createPlan([
            'installable' => $installable,
            'user' => Auth::user(),
            'affiliate_code' => $this->cart->appliedAffiliateCode(),
            'notes' => collect([
                $data['request_notes'] ?? null,
                ! empty($data['inspection_status']) ? 'Inspection: '.$data['inspection_status'] : null,
                ! empty($data['preferred_contact']) ? 'Preferred contact: '.$data['preferred_contact'] : null,
            ])->filter()->implode("\n"),
            'request_metadata' => [
                'ip' => $request->ip() ?? '0.0.0.0',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ],
        ]);

        if ($plan->status === 'pending_approval') {
            return redirect()->route('account.installments.show', ['plan' => $plan->reference])
                ->with('success', 'Your installment request has been submitted. Our team will review and respond within 24–48 hours.');
        }

        return redirect()->route('account.installments.show', ['plan' => $plan->reference])
            ->with('success', 'Plan created. Make your down payment to activate.');
    }

    /**
     * Initialize a gateway payment toward an installment plan.
     */
    public function initiatePayment(Request $request, InstallmentPlan $plan): RedirectResponse
    {
        abort_unless($plan->user_id === Auth::id(), 403);
        abort_unless($plan->canAcceptPayment(), 422, 'This plan does not accept payments right now.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_gateway' => ['required', 'in:paystack,flutterwave'],
        ]);

        $amount = (float) $data['amount'];
        $remaining = $plan->remainingAmount();
        if ($amount > $remaining + 0.01) {
            return back()->withErrors(['amount' => 'Payment exceeds remaining balance of ₦'.number_format($remaining, 2).'.']);
        }

        // First payment must clear the down payment minimum.
        if (! $plan->down_payment_paid && $amount < (float) $plan->minimum_down_payment_amount) {
            return back()->withErrors([
                'amount' => 'First payment must be at least ₦'.number_format((float) $plan->minimum_down_payment_amount, 2).' (the down payment).',
            ]);
        }

        $callback = route('installments.verify-payment', ['plan' => $plan->reference, 'gateway' => $data['payment_gateway']]);

        if ($data['payment_gateway'] === 'paystack') {
            /** @var PaystackGateway $gw */
            $gw = $this->gateways->for('paystack');
            $init = $gw->initializeForInstallment($plan, $amount, $callback);
        } else {
            /** @var FlutterwaveGateway $gw */
            $gw = $this->gateways->for('flutterwave');
            $init = $gw->initializeForInstallment($plan, $amount, $callback);
        }

        if (! $init) {
            return back()->withErrors(['payment_gateway' => 'We could not start your payment. Please try the other gateway.']);
        }

        return Inertia::location($init['authorization_url']);
    }

    /**
     * Gateway callback after the customer pays.
     */
    public function verifyPayment(Request $request, InstallmentPlan $plan): RedirectResponse
    {
        $gatewayName = $request->string('gateway')->toString() ?: 'paystack';
        $reference = $request->string('reference')->toString()
            ?: $request->string('tx_ref')->toString()
            ?: $request->string('trxref')->toString();

        $result = $this->gateways->for($gatewayName)->verify($reference);

        if (! $result->success) {
            return redirect()->route('account.installments.show', ['plan' => $plan->reference])
                ->with('error', 'Payment was not successful. You can try again from your plan page.');
        }

        $this->installments->recordPayment($plan, [
            'amount' => $result->amount ?? (float) $plan->minimum_down_payment_amount,
            'payment_gateway' => $gatewayName,
            'payment_reference' => $reference,
            'gateway_response' => $result->raw,
        ]);

        return redirect()->route('account.installments.show', ['plan' => $plan->reference])
            ->with('success', 'Payment received! Your plan has been updated.');
    }

    /**
     * @return array{0: ?Model, 1: ?string}
     */
    private function resolveInstallable(Request $request): array
    {
        $type = $request->string('type')->toString();
        $id = (int) $request->integer('id');

        $installable = $this->loadInstallable($type, $id);

        if (! $installable) {
            return [null, 'Item not found.'];
        }
        if (! $installable->installmentAvailableForPurchase()) {
            return [null, 'This item is not available for installment purchase.'];
        }

        return [$installable, null];
    }

    private function loadInstallable(string $type, int $id): ?Model
    {
        return match ($type) {
            'land' => Land::query()->find($id),
            'product' => Product::query()->find($id),
            'product_variant' => ProductVariant::query()->find($id),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function previewPricing(Model $installable, ?string $affiliateCode): array
    {
        $normalPrice = $this->normalPrice($installable);
        $affiliate = $affiliateCode ? $this->affiliates->findByCode($affiliateCode) : null;
        $affPrice = $this->affiliatePrice($installable);
        $useAffiliate = $affiliate && $affPrice !== null && $affPrice <= $normalPrice;

        $price = $useAffiliate ? (float) $affPrice : $normalPrice;
        $downPct = (int) ($installable->effectiveMinimumDownPaymentPercentage() ?? 30);
        $maxMonths = (int) ($installable->effectiveMaximumLengthMonths() ?? 6);
        $minDown = round($price * $downPct / 100, 2);
        $perMonth = $maxMonths > 0 ? round(($price - $minDown) / $maxMonths, 2) : 0;

        return [
            'normal_price' => $normalPrice,
            'price' => $price,
            'affiliate_applied' => $useAffiliate,
            'down_payment_percentage' => $downPct,
            'down_payment_amount' => $minDown,
            'maximum_length_months' => $maxMonths,
            'suggested_monthly' => $perMonth,
            'deadline_if_activated_today' => now()->copy()->addMonths($maxMonths)->toDateString(),
        ];
    }

    private function normalPrice(Model $i): float
    {
        if ($i instanceof Land) {
            return (float) ($i->price_per_plot * ($i->number_of_plots ?: 1));
        }
        if ($i instanceof ProductVariant) {
            return $i->effectivePrice();
        }
        if ($i instanceof Product) {
            return (float) $i->base_price;
        }
        return 0;
    }

    private function affiliatePrice(Model $i): ?float
    {
        if ($i instanceof ProductVariant) {
            return $i->effectiveAffiliatePrice();
        }
        if ($i instanceof Product) {
            return $i->affiliate_price !== null ? (float) $i->affiliate_price : null;
        }
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeInstallable(Model $i): array
    {
        if ($i instanceof Land) {
            return [
                'type' => 'land',
                'id' => $i->id,
                'label' => $i->title,
                'sublabel' => $i->location_address,
                'image' => $i->cover_url,
                'href' => "/lands/{$i->slug}",
            ];
        }
        if ($i instanceof ProductVariant) {
            return [
                'type' => 'product_variant',
                'id' => $i->id,
                'label' => $i->product?->name ?? 'Variant',
                'sublabel' => $i->attributes_label,
                'image' => $i->imageUrl(),
                'href' => $i->product ? "/{$i->product->subsidiary}/{$i->product->slug}" : null,
            ];
        }
        if ($i instanceof Product) {
            return [
                'type' => 'product',
                'id' => $i->id,
                'label' => $i->name,
                'sublabel' => null,
                'image' => $i->cover_url,
                'href' => "/{$i->subsidiary}/{$i->slug}",
            ];
        }
        return [];
    }
}

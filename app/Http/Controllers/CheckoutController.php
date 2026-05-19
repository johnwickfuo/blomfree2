<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\NigerianStates;
use App\Support\PaymentGateways\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly PaymentGatewayManager $gateways,
    ) {
    }

    public function index(): Response|RedirectResponse
    {
        $summary = $this->cart->summary();

        if ($summary['count'] < 1) {
            return redirect()->route('cart.index');
        }

        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'price', 'states', 'delivery_estimate_days']);

        return Inertia::render('Checkout/Index', [
            'cart' => $summary,
            'states' => NigerianStates::all(),
            'shippingZones' => $zones->map(fn (ShippingZone $z): array => [
                'id' => $z->id,
                'name' => $z->name,
                'price' => (float) $z->price,
                'states' => $z->states,
                'delivery_estimate_days' => $z->delivery_estimate_days,
            ])->values(),
            'pickupAddress' => Setting::get('pickup_address'),
            'whatsappNumber' => '2348103965317',
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Re-validate the cart in case stock changed between drawer and submit.
        $summary = $this->cart->summary();
        if ($summary['count'] < 1) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }
        if ($summary['has_issues']) {
            return redirect()->route('cart.index')
                ->with('error', 'Please resolve cart issues before placing an order.');
        }

        $zone = null;
        if ($data['delivery_method'] === 'delivery') {
            $zone = ShippingZone::findForState($data['delivery_state']);
            if (! $zone) {
                return back()->withErrors([
                    'delivery_state' => 'We have no shipping zone configured for this state — contact us for a delivery quote.',
                ])->withInput();
            }
        }

        $order = $this->orders->placeOrder($data, $data['payment_gateway'], $zone);

        $gateway = $this->gateways->for($data['payment_gateway']);
        $callbackUrl = route('checkout.verify', [
            'reference' => $order->reference,
            'gateway' => $gateway->name(),
        ]);

        $authUrl = $gateway->initialize($order, $callbackUrl);

        if ($authUrl === null) {
            return back()
                ->withErrors(['payment_gateway' => 'We could not start your payment. Please try again or pick the other gateway.'])
                ->withInput();
        }

        return Inertia::location($authUrl);
    }

    public function verify(Request $request, string $reference): RedirectResponse
    {
        $order = Order::query()->where('reference', $reference)->firstOrFail();
        $gatewayName = $request->string('gateway')->toString() ?: $order->payment_gateway;

        $result = $this->gateways->for($gatewayName)->verify($reference);

        if (! $result->success) {
            $order->update(['payment_status' => 'failed']);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment was not successful. Your cart has been kept — feel free to try again.');
        }

        $this->orders->markPaid($order, $result->transactionId);

        return redirect()->route('orders.show', ['order' => $order->reference]);
    }
}

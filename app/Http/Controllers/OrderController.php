<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load('items');

        return Inertia::render('Orders/Show', [
            'order' => $this->orderPayload($order),
        ]);
    }

    public function trackForm(): Response
    {
        return Inertia::render('Orders/Track', [
            'order' => null,
        ]);
    }

    public function trackLookup(Request $request): Response
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $order = Order::query()
            ->where('reference', $data['reference'])
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($data['email'])])
            ->with('items')
            ->first();

        return Inertia::render('Orders/Track', [
            'order' => $order ? $this->orderPayload($order) : null,
            'lookup' => $data,
            'notFound' => $order === null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        return [
            'reference' => $order->reference,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'delivery_address' => $order->delivery_address,
            'delivery_state' => $order->delivery_state,
            'delivery_lga' => $order->delivery_lga,
            'delivery_notes' => $order->delivery_notes,
            'delivery_method' => $order->delivery_method,
            'shipping_fee' => (float) $order->shipping_fee,
            'subtotal' => (float) $order->subtotal,
            'total' => (float) $order->total,
            'payment_gateway' => $order->payment_gateway,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'tracking_notes' => $order->tracking_notes,
            'placed_at' => $order->placed_at?->toIso8601String(),
            'paid_at' => $order->paid_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'name' => $item->item_name,
                'label' => $item->item_label,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'image' => $item->meta['image'] ?? null,
            ])->values(),
        ];
    }
}

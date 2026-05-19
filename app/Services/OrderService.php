<?php

namespace App\Services;

use App\Mail\AnimalOrderReceivedAdmin;
use App\Mail\OrderCancelledCustomer;
use App\Mail\OrderDeliveredCustomer;
use App\Mail\OrderPaidCustomer;
use App\Mail\OrderPlacedAdmin;
use App\Mail\OrderPlacedCustomer;
use App\Mail\OrderShippedCustomer;
use App\Models\Animal;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly AffiliateService $affiliates,
    ) {
    }

    /**
     * Create a pending-payment order from the current cart. Snapshots prices
     * and labels into order_items so the order is preserved if the cart or
     * the underlying products change later.
     *
     * @param  array<string, mixed>  $delivery
     */
    public function placeOrder(array $delivery, string $gateway, ?ShippingZone $zone): Order
    {
        $items = $this->cart->getItems();

        if ($items->isEmpty()) {
            abort(422, 'Your cart is empty.');
        }

        // Re-validate affiliate code at submit time (covers cases where the
        // affiliate was suspended between code-apply and checkout).
        $affiliate = $this->cart->appliedAffiliate();

        // Self-referral block: an affiliate can't get their own commission
        // by buying with their own code.
        if ($affiliate && strtolower($delivery['customer_email']) === strtolower((string) $affiliate->user?->email)) {
            $affiliate = null;
            $this->cart->clearAffiliateCode();
        }

        $subtotal = (float) $items->sum(fn (CartItem $i): float => (float) $i->price_snapshot * (int) $i->quantity);
        $shippingFee = $delivery['delivery_method'] === 'delivery' && $zone ? (float) $zone->price : 0;
        $total = $subtotal + $shippingFee;

        // Compute affiliate totals from cart meta.
        $discountTotal = 0.0;
        $commissionTotal = 0.0;
        foreach ($items as $item) {
            $meta = $item->meta ?? [];
            $eligible = (bool) ($meta['affiliate_eligible'] ?? false) && $affiliate !== null;
            $normalPrice = (float) ($meta['normal_price'] ?? $item->price_snapshot);
            if ($eligible) {
                $discountTotal += max(0, ($normalPrice - (float) $item->price_snapshot) * (int) $item->quantity);
                $commissionTotal += (float) ($meta['affiliate_unit_commission'] ?? 0) * (int) $item->quantity;
            }
        }

        return DB::transaction(function () use ($delivery, $gateway, $zone, $items, $subtotal, $shippingFee, $total, $affiliate, $discountTotal, $commissionTotal): Order {
            $order = Order::create([
                'customer_name' => $delivery['customer_name'],
                'customer_email' => $delivery['customer_email'],
                'customer_phone' => $delivery['customer_phone'],
                'delivery_address' => $delivery['delivery_address'],
                'delivery_state' => $delivery['delivery_state'],
                'delivery_lga' => $delivery['delivery_lga'] ?? null,
                'delivery_notes' => $delivery['delivery_notes'] ?? null,
                'delivery_method' => $delivery['delivery_method'],
                'shipping_zone_id' => $zone?->id,
                'shipping_fee' => $shippingFee,
                'subtotal' => $subtotal,
                'total' => $total,
                'payment_gateway' => $gateway,
                'payment_status' => 'pending',
                'order_status' => 'pending_payment',
                'placed_at' => now(),
                'affiliate_id' => $affiliate?->id,
                'affiliate_code_used' => $affiliate?->code,
                'affiliate_discount_total' => $discountTotal,
                'affiliate_commission_total' => $commissionTotal,
            ]);

            foreach ($items as $item) {
                $cartable = $item->cartable;
                $meta = $item->meta ?? [];
                $eligible = (bool) ($meta['affiliate_eligible'] ?? false) && $affiliate !== null;
                $normalPrice = (float) ($meta['normal_price'] ?? $item->price_snapshot);

                $order->items()->create([
                    'orderable_type' => $item->cartable_type,
                    'orderable_id' => $item->cartable_id,
                    'item_name' => $this->itemName($cartable, $meta),
                    'item_label' => $this->itemLabel($cartable, $meta),
                    'unit_price' => (string) $item->price_snapshot,
                    'quantity' => $item->quantity,
                    'subtotal' => (string) ((float) $item->price_snapshot * (int) $item->quantity),
                    'meta' => $this->itemMeta($cartable, $meta),
                    'normal_unit_price' => (string) $normalPrice,
                    'affiliate_unit_commission' => (string) ($eligible ? (float) ($meta['affiliate_unit_commission'] ?? 0) : 0),
                    'affiliate_eligible' => $eligible,
                ]);
            }

            // Notify customer + admin that the order has been received and is
            // awaiting payment.
            $this->safeMail($order->customer_email, new OrderPlacedCustomer($order));
            $adminEmail = Setting::get('admin_notification_email', 'admin@blomfree.com');
            $this->safeMail($adminEmail, new OrderPlacedAdmin($order));

            // Live animals need a fast human response — send a separate
            // urgent admin email so it stands out in the inbox.
            if ($order->hasAnimals()) {
                $this->safeMail($adminEmail, new AnimalOrderReceivedAdmin($order));
            }

            return $order;
        });
    }

    /**
     * Mark an order paid. Idempotent: if the order is already paid, returns
     * false and is a no-op. Otherwise decrements stock atomically, clears the
     * cart and sends the "payment received" email.
     */
    public function markPaid(Order $order, ?string $gatewayTransactionId): bool
    {
        return DB::transaction(function () use ($order, $gatewayTransactionId): bool {
            // Re-load with a row lock so concurrent verify + webhook calls
            // can't both decrement stock.
            $fresh = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->isPaid()) {
                return false;
            }

            foreach ($fresh->items()->with('orderable')->get() as $orderItem) {
                $this->decrementStock($orderItem->orderable, (int) $orderItem->quantity);
            }

            $fresh->update([
                'payment_status' => 'paid',
                'payment_reference' => $gatewayTransactionId,
                'order_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Empty whichever session cart placed the order — best-effort,
            // since the buyer may be back on the site already.
            $this->cart->clear();

            $this->safeMail($fresh->customer_email, new OrderPaidCustomer($fresh));

            // Affiliate accrual is best-effort and idempotent — never let it
            // block payment confirmation.
            try {
                $fresh->loadMissing('items');
                $this->affiliates->accrueForPaidOrder($fresh);
            } catch (Throwable $e) {
                report($e);
            }

            return true;
        });
    }

    /**
     * Move the order to a new status. Optionally records tracking notes
     * (used by "Mark Shipped"). Sends the customer the matching email.
     */
    public function markStatus(Order $order, string $status, ?string $trackingNotes = null): void
    {
        $payload = ['order_status' => $status];
        if ($trackingNotes !== null) {
            $payload['tracking_notes'] = $trackingNotes;
        }
        if ($status === 'delivered') {
            $payload['delivered_at'] = now();
        }

        $order->update($payload);
        $order->refresh();

        match ($status) {
            'shipped' => $this->safeMail($order->customer_email, new OrderShippedCustomer($order)),
            'delivered' => $this->safeMail($order->customer_email, new OrderDeliveredCustomer($order)),
            default => null,
        };

        if ($status === 'delivered') {
            try {
                $this->affiliates->onOrderDelivered($order);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Cancel an order. Restocks items if the order had been paid, so the
     * inventory is honest again. Refunds are handled manually by the admin.
     */
    public function cancel(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason): void {
            $fresh = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh) {
                return;
            }

            $wasPaid = $fresh->isPaid();

            if ($wasPaid) {
                foreach ($fresh->items()->with('orderable')->get() as $orderItem) {
                    $this->restock($orderItem->orderable, (int) $orderItem->quantity);
                }
            }

            $fresh->update([
                'order_status' => 'cancelled',
                'payment_status' => $wasPaid ? $fresh->payment_status : 'cancelled',
                'admin_notes' => trim(($fresh->admin_notes ? $fresh->admin_notes."\n" : '').'Cancelled: '.($reason ?: 'no reason given')),
            ]);

            $this->safeMail($fresh->customer_email, new OrderCancelledCustomer($fresh, $reason));

            try {
                $this->affiliates->reverseForOrder($fresh, 'Order cancelled: '.($reason ?: 'no reason given'));
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    /**
     * Mark an already-paid order as refunded. The gateway refund itself is
     * handled out-of-band by the admin for now. Logs an OrderRefund row for
     * the audit trail.
     */
    public function markRefunded(Order $order, float $amount, ?string $notes = null): void
    {
        DB::transaction(function () use ($order, $amount, $notes): void {
            $fresh = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $fresh) {
                return;
            }

            foreach ($fresh->items()->with('orderable')->get() as $orderItem) {
                $this->restock($orderItem->orderable, (int) $orderItem->quantity);
            }

            $fresh->refunds()->create([
                'user_id' => auth()->id(),
                'amount' => (string) $amount,
                'notes' => $notes,
            ]);

            $summary = '₦'.number_format($amount, 2).($notes ? ' — '.$notes : '');
            $fresh->update([
                'order_status' => 'refunded',
                'admin_notes' => trim(($fresh->admin_notes ? $fresh->admin_notes."\n" : '').'Refunded: '.$summary),
            ]);

            $this->safeMail($fresh->customer_email, new OrderCancelledCustomer($fresh, $summary));

            try {
                $this->affiliates->reverseForOrder($fresh, 'Order refunded: '.$summary);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    private function decrementStock(?Model $orderable, int $quantity): void
    {
        if ($orderable === null) {
            return;
        }

        if ($orderable instanceof ProductVariant) {
            $fresh = ProductVariant::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => max(0, (int) $fresh->stock - $quantity)]);
            }

            return;
        }

        if ($orderable instanceof Product) {
            $fresh = Product::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => max(0, (int) ($fresh->stock ?? 0) - $quantity)]);
            }

            return;
        }

        if ($orderable instanceof Animal) {
            $fresh = Animal::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if (! $fresh) {
                return;
            }

            if ($fresh->listing_type === 'pool') {
                $fresh->update(['stock' => max(0, (int) ($fresh->stock ?? 0) - $quantity)]);
            } else {
                $fresh->update(['availability' => 'sold']);
            }
        }
    }

    private function restock(?Model $orderable, int $quantity): void
    {
        if ($orderable === null) {
            return;
        }

        if ($orderable instanceof ProductVariant) {
            $fresh = ProductVariant::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => (int) $fresh->stock + $quantity]);
            }

            return;
        }

        if ($orderable instanceof Product) {
            $fresh = Product::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => (int) ($fresh->stock ?? 0) + $quantity]);
            }

            return;
        }

        if ($orderable instanceof Animal) {
            $fresh = Animal::query()->whereKey($orderable->id)->lockForUpdate()->first();
            if (! $fresh) {
                return;
            }

            if ($fresh->listing_type === 'pool') {
                $fresh->update(['stock' => (int) ($fresh->stock ?? 0) + $quantity]);
            } else {
                $fresh->update(['availability' => 'available']);
            }
        }
    }

    private function itemName(?Model $cartable, array $meta): string
    {
        if ($cartable instanceof ProductVariant) {
            return $cartable->product?->name ?? ($meta['product_name'] ?? 'Product');
        }
        if ($cartable instanceof Product) {
            return $cartable->name;
        }
        if ($cartable instanceof Animal) {
            return $cartable->name;
        }

        return $meta['product_name'] ?? $meta['name'] ?? 'Item';
    }

    private function itemLabel(?Model $cartable, array $meta): ?string
    {
        if ($cartable instanceof ProductVariant) {
            return $cartable->attributes_label ?: ($meta['variant_label'] ?? null);
        }
        if ($cartable instanceof Animal) {
            return $this->animalLabel($cartable);
        }

        return $meta['variant_label'] ?? null;
    }

    /**
     * Rich, customer-friendly label for animals on the order line, e.g.
     * "German Shepherd dog, 4 months, Male" or "New Zealand White rabbit".
     */
    private function animalLabel(Animal $animal): string
    {
        $species = match ($animal->category) {
            'dog' => 'dog',
            'cat' => 'cat',
            'rabbit' => 'rabbit',
            'grasscutter' => 'grasscutter',
            default => 'animal',
        };

        if ($animal->listing_type === 'pool') {
            return trim("{$animal->breed} {$species}");
        }

        $parts = [trim("{$animal->breed} {$species}")];

        if ($animal->age_text) {
            $parts[] = $animal->age_text;
        }
        if ($animal->sex && $animal->sex !== 'mixed') {
            $parts[] = ucfirst($animal->sex);
        }

        return implode(', ', $parts);
    }

    /**
     * Snapshot enough display info that the order can be rendered even if
     * the underlying record is later edited or deleted.
     *
     * @return array<string, mixed>
     */
    private function itemMeta(?Model $cartable, array $meta): array
    {
        $base = [];

        if ($cartable instanceof ProductVariant) {
            $base = [
                'attributes' => $cartable->attributes,
                'sku' => $cartable->sku,
                'product_slug' => $cartable->product?->slug,
                'subsidiary' => $cartable->product?->subsidiary,
                'image' => $cartable->imageUrl(),
            ];
        } elseif ($cartable instanceof Product) {
            $base = [
                'product_slug' => $cartable->slug,
                'subsidiary' => $cartable->subsidiary,
                'image' => $cartable->cover_url,
            ];
        } elseif ($cartable instanceof Animal) {
            $base = [
                'animal_slug' => $cartable->slug,
                'listing_type' => $cartable->listing_type,
                'category' => $cartable->category,
                'breed' => $cartable->breed,
                'image' => $cartable->cover_url,
            ];
        }

        return array_merge($meta, $base);
    }

    private function safeMail(?string $address, object $mailable): void
    {
        if (! $address) {
            return;
        }

        try {
            Mail::to($address)->send($mailable);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

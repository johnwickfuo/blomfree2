<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\SessionManager;

class CartService
{
    public const AFFILIATE_SESSION_KEY = 'affiliate_code';

    public function __construct(private readonly SessionManager $session)
    {
    }

    public function getOrCreate(): Cart
    {
        return Cart::firstOrCreate(['session_id' => $this->session->getId()]);
    }

    public function appliedAffiliateCode(): ?string
    {
        $code = $this->session->get(self::AFFILIATE_SESSION_KEY);

        return $code ? strtoupper((string) $code) : null;
    }

    public function applyAffiliateCode(string $code): void
    {
        $this->session->put(self::AFFILIATE_SESSION_KEY, strtoupper(trim($code)));
    }

    public function clearAffiliateCode(): void
    {
        $this->session->forget(self::AFFILIATE_SESSION_KEY);
    }

    /**
     * Resolve the currently-applied affiliate, if the code is valid + active.
     */
    public function appliedAffiliate(): ?\App\Models\Affiliate
    {
        $code = $this->appliedAffiliateCode();
        if (! $code) {
            return null;
        }

        return app(\App\Services\AffiliateService::class)->findByCode($code);
    }

    public function add(Model $cartable, int $quantity = 1): CartItem
    {
        $cart = $this->getOrCreate();
        $rule = $this->ruleFor($cartable);

        if ($rule['max'] < 1) {
            abort(422, 'This item is not available for purchase right now.');
        }

        $existing = $cart->items()
            ->where('cartable_type', $cartable->getMorphClass())
            ->where('cartable_id', $cartable->getKey())
            ->first();

        $requested = $existing ? $existing->quantity + $quantity : $quantity;
        $quantity = max(1, min($requested, $rule['max']));

        if ($existing) {
            $existing->update([
                'quantity' => $quantity,
                'price_snapshot' => (string) $rule['price'],
                'meta' => $rule['meta'],
            ]);
            $item = $existing;
        } else {
            $item = $cart->items()->create([
                'cartable_type' => $cartable->getMorphClass(),
                'cartable_id' => $cartable->getKey(),
                'quantity' => $quantity,
                'price_snapshot' => (string) $rule['price'],
                'meta' => $rule['meta'],
            ]);
        }

        $cart->touch();

        return $item;
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        $cartable = $item->cartable;
        $rule = $cartable
            ? $this->ruleFor($cartable)
            : ['max' => 0, 'price' => (float) $item->price_snapshot, 'meta' => $item->meta ?? []];

        $quantity = max(1, min($quantity, max(1, $rule['max'])));
        $item->update(['quantity' => $quantity]);
        $item->cart?->touch();

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $cart = $item->cart;
        $item->delete();
        $cart?->touch();
    }

    public function clear(): void
    {
        $cart = $this->getOrCreate();
        $cart->items()->delete();
        $cart->touch();
    }

    public function getItems(): Collection
    {
        return $this->getOrCreate()
            ->items()
            ->with(['cartable' => fn ($morphTo) => $morphTo->morphWith([
                ProductVariant::class => ['product.media', 'media'],
                Product::class => ['media'],
                Animal::class => ['media'],
            ])])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Re-evaluate every cart line against the current ruleFor() so that
     * applying or removing an affiliate code swaps in the correct price.
     */
    public function repriceAll(): void
    {
        foreach ($this->getItems() as $item) {
            $cartable = $item->cartable;
            if (! $cartable) {
                continue;
            }
            $rule = $this->ruleFor($cartable);
            $item->update([
                'price_snapshot' => (string) $rule['price'],
                'meta' => array_merge($item->meta ?? [], $rule['meta']),
            ]);
        }
        $this->getOrCreate()->touch();
    }

    public function subtotal(): float
    {
        return (float) $this->getItems()->sum(fn (CartItem $item): float => $item->lineTotal());
    }

    public function count(): int
    {
        return (int) $this->getItems()->sum('quantity');
    }

    /**
     * Re-check stock, availability and price for each item. Returns a map
     * of cart_item_id => issue message for any items that need attention.
     *
     * @return array<int, string>
     */
    public function validate(): array
    {
        $issues = [];

        foreach ($this->getItems() as $item) {
            $cartable = $item->cartable;

            if ($cartable === null) {
                $issues[$item->id] = 'This item is no longer available.';
                continue;
            }

            $rule = $this->ruleFor($cartable);

            if ($rule['max'] < 1) {
                $issues[$item->id] = 'This item is now out of stock.';
            } elseif ($item->quantity > $rule['max']) {
                $issues[$item->id] = "Only {$rule['max']} available — please reduce the quantity.";
            } elseif (abs((float) $item->price_snapshot - $rule['price']) > 0.01) {
                $issues[$item->id] = 'The price has changed since you added this item.';
            }
        }

        return $issues;
    }

    /**
     * Summary payload shaped for the cart drawer + full page + header badge.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $items = $this->getItems();
        $issues = $this->validate();
        $payload = [];

        $affiliate = $this->appliedAffiliate();
        $totalDiscount = 0.0;

        foreach ($items as $item) {
            $cartable = $item->cartable;
            $rule = $cartable
                ? $this->ruleFor($cartable)
                : ['max' => 0, 'price' => (float) $item->price_snapshot, 'meta' => $item->meta ?? []];

            $display = $this->display($item);
            $normalPrice = (float) ($rule['meta']['normal_price'] ?? $item->price_snapshot);
            $eligible = (bool) ($rule['meta']['affiliate_eligible'] ?? false);
            $discount = $eligible
                ? max(0, ($normalPrice - (float) $item->price_snapshot) * (int) $item->quantity)
                : 0;
            $totalDiscount += $discount;

            $payload[] = [
                'id' => $item->id,
                'cartable_type' => $item->cartable_type,
                'quantity' => $item->quantity,
                'max_quantity' => $rule['max'],
                'price' => (float) $item->price_snapshot,
                'normal_price' => $normalPrice,
                'affiliate_eligible' => $eligible,
                'affiliate_discount' => $discount,
                'line_total' => $item->lineTotal(),
                'name' => $display['name'],
                'variant_label' => $display['variant_label'],
                'image' => $display['image'],
                'href' => $display['href'],
                'issue' => $issues[$item->id] ?? null,
            ];
        }

        return [
            'count' => array_sum(array_column($payload, 'quantity')),
            'subtotal' => array_sum(array_column($payload, 'line_total')),
            'items' => $payload,
            'has_issues' => $issues !== [],
            'has_animals' => $items->contains(
                fn (CartItem $item): bool => $item->cartable_type === Animal::class,
            ),
            'affiliate' => $affiliate ? [
                'code' => $affiliate->code,
                'discount_total' => $totalDiscount,
                'eligible_item_count' => count(array_filter($payload, fn ($p): bool => $p['affiliate_eligible'])),
            ] : null,
            'applied_affiliate_code' => $this->appliedAffiliateCode(),
        ];
    }

    /**
     * @return array{max: int, price: float, meta: array<string, mixed>}
     */
    private function ruleFor(Model $cartable): array
    {
        $affiliateActive = $this->appliedAffiliate() !== null;

        if ($cartable instanceof ProductVariant) {
            $normalPrice = $cartable->effectivePrice();
            $affPrice = $affiliateActive ? $cartable->effectiveAffiliatePrice() : null;
            $affCommission = $affiliateActive ? $cartable->effectiveAffiliateCommission() : null;
            $eligible = $affiliateActive && $cartable->isAffiliateEnabled() && $affPrice !== null && $affPrice <= $normalPrice;

            return [
                'max' => (int) $cartable->stock,
                'price' => $eligible ? $affPrice : $normalPrice,
                'meta' => [
                    'product_name' => $cartable->product?->name,
                    'variant_label' => $cartable->attributes_label,
                    'variant_attributes' => $cartable->attributes,
                    'normal_price' => $normalPrice,
                    'affiliate_eligible' => $eligible,
                    'affiliate_unit_commission' => $eligible ? (float) $affCommission : 0.0,
                ],
            ];
        }

        if ($cartable instanceof Product) {
            $normalPrice = (float) $cartable->base_price;
            $eligible = $affiliateActive
                && ! $cartable->has_variants
                && $cartable->isAffiliateEnabled()
                && $cartable->affiliate_price !== null
                && (float) $cartable->affiliate_price <= $normalPrice;
            $price = $eligible ? (float) $cartable->affiliate_price : $normalPrice;

            return [
                'max' => $cartable->has_variants ? 0 : (int) ($cartable->stock ?? 0),
                'price' => $price,
                'meta' => [
                    'product_name' => $cartable->name,
                    'normal_price' => $normalPrice,
                    'affiliate_eligible' => $eligible,
                    'affiliate_unit_commission' => $eligible ? (float) $cartable->affiliate_commission : 0.0,
                ],
            ];
        }

        if ($cartable instanceof Animal) {
            $normalPrice = (float) $cartable->price;
            $eligible = $affiliateActive
                && method_exists($cartable, 'isAffiliateEnabled')
                && $cartable->isAffiliateEnabled()
                && $cartable->affiliate_price !== null
                && (float) $cartable->affiliate_price <= $normalPrice;
            $price = $eligible ? (float) $cartable->affiliate_price : $normalPrice;

            $baseMeta = [
                'breed' => $cartable->breed,
                'category' => $cartable->category,
                'normal_price' => $normalPrice,
                'affiliate_eligible' => $eligible,
                'affiliate_unit_commission' => $eligible ? (float) $cartable->affiliate_commission : 0.0,
            ];

            if ($cartable->listing_type === 'pool') {
                return [
                    'max' => (int) ($cartable->stock ?? 0),
                    'price' => $price,
                    'meta' => $baseMeta,
                ];
            }

            $sellable = $cartable->availability === 'available'
                && $cartable->supports_online_purchase === true;

            return [
                'max' => $sellable ? 1 : 0,
                'price' => $price,
                'meta' => $baseMeta,
            ];
        }

        return ['max' => 0, 'price' => 0.0, 'meta' => []];
    }

    /**
     * @return array{name: string, variant_label: ?string, image: ?string, href: ?string}
     */
    private function display(CartItem $item): array
    {
        $cartable = $item->cartable;
        $meta = $item->meta ?? [];

        if ($cartable instanceof ProductVariant) {
            $product = $cartable->product;

            return [
                'name' => $product?->name ?? ($meta['product_name'] ?? 'Product'),
                'variant_label' => $cartable->attributes_label ?: ($meta['variant_label'] ?? null),
                'image' => $cartable->imageUrl(),
                'href' => $product ? "/{$product->subsidiary}/{$product->slug}" : null,
            ];
        }

        if ($cartable instanceof Product) {
            return [
                'name' => $cartable->name,
                'variant_label' => null,
                'image' => $cartable->cover_url,
                'href' => "/{$cartable->subsidiary}/{$cartable->slug}",
            ];
        }

        if ($cartable instanceof Animal) {
            return [
                'name' => $cartable->name,
                'variant_label' => $cartable->breed,
                'image' => $cartable->cover_url,
                'href' => "/kennel-farm/{$cartable->slug}",
            ];
        }

        return [
            'name' => $meta['product_name'] ?? $meta['name'] ?? 'Item',
            'variant_label' => $meta['variant_label'] ?? null,
            'image' => null,
            'href' => null,
        ];
    }
}

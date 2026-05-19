// Thin wrappers around GA4 (gtag) and Facebook Pixel (fbq) for the
// e-commerce events the site cares about. Each is a no-op when the
// underlying tag isn't loaded, so the site works the same in dev.

type Currency = 'NGN';

declare global {
    interface Window {
        gtag?: (...args: unknown[]) => void;
        fbq?: (...args: unknown[]) => void;
    }
}

interface ItemPayload {
    id: string | number;
    name: string;
    price?: number;
    quantity?: number;
    category?: string;
}

function gtagEvent(name: string, params: Record<string, unknown>): void {
    if (typeof window === 'undefined' || typeof window.gtag !== 'function') return;
    window.gtag('event', name, params);
}

function fbqTrack(name: string, params: Record<string, unknown> = {}): void {
    if (typeof window === 'undefined' || typeof window.fbq !== 'function') return;
    window.fbq('track', name, params);
}

export function trackViewItem(
    item: ItemPayload,
    currency: Currency = 'NGN',
): void {
    gtagEvent('view_item', {
        currency,
        value: item.price ?? 0,
        items: [
            {
                item_id: String(item.id),
                item_name: item.name,
                item_category: item.category,
                price: item.price ?? 0,
                quantity: 1,
            },
        ],
    });
    fbqTrack('ViewContent', {
        content_ids: [String(item.id)],
        content_name: item.name,
        content_type: 'product',
        currency,
        value: item.price ?? 0,
    });
}

export function trackAddToCart(
    item: ItemPayload,
    currency: Currency = 'NGN',
): void {
    const value = (item.price ?? 0) * (item.quantity ?? 1);
    gtagEvent('add_to_cart', {
        currency,
        value,
        items: [
            {
                item_id: String(item.id),
                item_name: item.name,
                item_category: item.category,
                price: item.price ?? 0,
                quantity: item.quantity ?? 1,
            },
        ],
    });
    fbqTrack('AddToCart', {
        content_ids: [String(item.id)],
        content_name: item.name,
        content_type: 'product',
        currency,
        value,
    });
}

export function trackBeginCheckout(
    total: number,
    items: ItemPayload[],
    currency: Currency = 'NGN',
): void {
    gtagEvent('begin_checkout', {
        currency,
        value: total,
        items: items.map((i) => ({
            item_id: String(i.id),
            item_name: i.name,
            price: i.price ?? 0,
            quantity: i.quantity ?? 1,
        })),
    });
    fbqTrack('InitiateCheckout', {
        contents: items.map((i) => ({ id: String(i.id), quantity: i.quantity ?? 1 })),
        currency,
        value: total,
    });
}

export function trackPurchase(
    reference: string,
    total: number,
    items: ItemPayload[],
    currency: Currency = 'NGN',
): void {
    gtagEvent('purchase', {
        transaction_id: reference,
        currency,
        value: total,
        items: items.map((i) => ({
            item_id: String(i.id),
            item_name: i.name,
            price: i.price ?? 0,
            quantity: i.quantity ?? 1,
        })),
    });
    fbqTrack('Purchase', {
        contents: items.map((i) => ({ id: String(i.id), quantity: i.quantity ?? 1 })),
        currency,
        value: total,
    });
}

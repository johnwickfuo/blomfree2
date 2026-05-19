/**
 * Fire a custom event the AppLayout listens for to slide the cart drawer
 * open. Used after any successful "Add to Cart" action across the site.
 */
export const CART_OPEN_EVENT = 'cart:open';

export function openCartDrawer(): void {
    window.dispatchEvent(new CustomEvent(CART_OPEN_EVENT));
}

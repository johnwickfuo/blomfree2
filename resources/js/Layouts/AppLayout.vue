<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X, ShoppingCart, Instagram, Facebook, AlertTriangle } from 'lucide-vue-next';
import CartLineItem from '@/Components/CartLineItem.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';
import { CART_OPEN_EVENT } from '@/lib/cart';
import type { CartData } from '@/types/models';

interface NavLink {
    label: string;
    href: string;
}

const navLinks: NavLink[] = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Real Estate', href: '/lands' },
    { label: 'Kennel & Farm', href: '/kennel-farm' },
    { label: 'Collections', href: '/collections' },
    { label: 'Gadgets', href: '/gadgets' },
    { label: 'Contact', href: '/contact' },
];

const phones = [
    { display: '0810 396 5317', tel: '+2348103965317', wa: '2348103965317' },
    { display: '0813 499 1052', tel: '+2348134991052', wa: '2348134991052' },
];

const whatsappPrimary = 'https://wa.me/2348103965317';
const year = new Date().getFullYear();

const mobileOpen = ref(false);
const cartOpen = ref(false);
const scrolled = ref(false);

const page = usePage();

const cart = computed<CartData>(() => page.props.cart);
const siteLogo = computed<string | null>(() => {
    const url = (page.props.branding as { logo_url?: string | null } | undefined)?.logo_url;
    return url || null;
});

const isActive = (href: string): boolean => {
    if (href === '/') return page.url === '/';
    return page.url.startsWith(href);
};

const onCartOpenEvent = (): void => {
    cartOpen.value = true;
};

const closeAll = (): void => {
    mobileOpen.value = false;
    cartOpen.value = false;
};

const panelOpen = computed(() => mobileOpen.value || cartOpen.value);

watch(panelOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

const onScroll = (): void => {
    scrolled.value = window.scrollY > 8;
};

const onKeydown = (e: KeyboardEvent): void => {
    if (e.key === 'Escape') closeAll();
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('keydown', onKeydown);
    window.addEventListener(CART_OPEN_EVENT, onCartOpenEvent);
    onScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener(CART_OPEN_EVENT, onCartOpenEvent);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-brand-cream text-brand-dark">
        <!-- Header -->
        <header
            class="sticky top-0 z-30 bg-brand-dark text-white transition-shadow duration-300"
            :class="
                scrolled ? 'shadow-lg shadow-black/30' : 'border-b border-white/10'
            "
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:h-20 sm:px-6 lg:px-8"
            >
                <!-- Wordmark -->
                <Link
                    href="/"
                    class="flex items-center gap-2.5"
                    @click="closeAll"
                >
                    <img
                        v-if="siteLogo"
                        :src="siteLogo"
                        alt="BLOMFREE & CO."
                        class="h-12 w-auto max-w-[180px] shrink-0 object-contain sm:h-14 sm:max-w-[220px]"
                    />
                    <template v-else>
                        <!-- Inline SVG bird placeholder (used until an admin uploads a logo) -->
                        <svg
                            viewBox="0 0 32 32"
                            fill="currentColor"
                            class="h-7 w-7 shrink-0 text-brand-orange sm:h-8 sm:w-8"
                            aria-hidden="true"
                        >
                            <path
                                d="M31 7.3c-1.1.5-2.3.8-3.5 1 1.3-.8 2.2-2 2.7-3.4-1.2.7-2.5 1.2-3.9 1.5C25.1 5.2 23.5 4.5 21.8 4.5c-3.4 0-6.1 2.7-6.1 6.1 0 .5.05.95.15 1.4-5.1-.25-9.6-2.7-12.6-6.4-.5.9-.85 2-.85 3.1 0 2.1 1.07 4 2.7 5.1-1-.03-1.95-.3-2.77-.76v.08c0 3 2.1 5.5 4.9 6-.5.15-1.05.2-1.6.2-.4 0-.78-.04-1.15-.1.78 2.4 3 4.2 5.65 4.25-2.07 1.62-4.68 2.6-7.5 2.6-.5 0-.97-.03-1.45-.08C3.9 27.9 7.1 29 10.55 29c11.45 0 17.7-9.5 17.7-17.7v-.8c1.2-.88 2.27-1.98 3.1-3.2z"
                            />
                        </svg>
                        <span
                            class="text-lg font-extrabold tracking-tight sm:text-xl"
                        >
                            BLOMFREE
                        </span>
                    </template>
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden items-center gap-1 lg:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="rounded-full px-3.5 py-2 text-sm font-semibold transition-colors"
                        :class="
                            isActive(link.href)
                                ? 'text-brand-orange'
                                : 'text-white/80 hover:text-white'
                        "
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <!-- Right cluster: cart + hamburger -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <button
                        type="button"
                        class="relative rounded-full p-2.5 text-white/90 transition-colors hover:bg-white/10 hover:text-white"
                        aria-label="Open cart"
                        @click="cartOpen = true"
                    >
                        <ShoppingCart class="h-5 w-5" />
                        <span
                            v-if="cart.count > 0"
                            class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-brand-orange px-1 text-[11px] font-bold leading-none text-white"
                        >
                            {{ cart.count > 99 ? '99+' : cart.count }}
                        </span>
                    </button>

                    <button
                        type="button"
                        class="rounded-full p-2.5 text-white/90 transition-colors hover:bg-white/10 hover:text-white lg:hidden"
                        aria-label="Open menu"
                        @click="mobileOpen = true"
                    >
                        <Menu class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main id="main-content" tabindex="-1" class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-brand-dark text-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Brand -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-2.5">
                            <img
                                v-if="siteLogo"
                                :src="siteLogo"
                                alt="BLOMFREE & CO."
                                class="h-14 w-auto max-w-[220px] object-contain"
                            />
                            <template v-else>
                                <svg
                                    viewBox="0 0 32 32"
                                    fill="currentColor"
                                    class="h-7 w-7 text-brand-orange"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M31 7.3c-1.1.5-2.3.8-3.5 1 1.3-.8 2.2-2 2.7-3.4-1.2.7-2.5 1.2-3.9 1.5C25.1 5.2 23.5 4.5 21.8 4.5c-3.4 0-6.1 2.7-6.1 6.1 0 .5.05.95.15 1.4-5.1-.25-9.6-2.7-12.6-6.4-.5.9-.85 2-.85 3.1 0 2.1 1.07 4 2.7 5.1-1-.03-1.95-.3-2.77-.76v.08c0 3 2.1 5.5 4.9 6-.5.15-1.05.2-1.6.2-.4 0-.78-.04-1.15-.1.78 2.4 3 4.2 5.65 4.25-2.07 1.62-4.68 2.6-7.5 2.6-.5 0-.97-.03-1.45-.08C3.9 27.9 7.1 29 10.55 29c11.45 0 17.7-9.5 17.7-17.7v-.8c1.2-.88 2.27-1.98 3.1-3.2z"
                                    />
                                </svg>
                                <span class="text-xl font-extrabold tracking-tight">
                                    BLOMFREE
                                </span>
                            </template>
                        </div>
                        <p
                            class="mt-3 text-sm font-semibold text-brand-orange"
                        >
                            Quality is Priceless
                        </p>
                        <p class="mt-2 text-sm text-white/60">
                            BLOMFREE &amp; CO. NIG. LTD
                        </p>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3
                            class="text-sm font-bold uppercase tracking-wider text-white/90"
                        >
                            Contact
                        </h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li
                                v-for="phone in phones"
                                :key="phone.tel"
                                class="flex flex-col gap-0.5"
                            >
                                <a
                                    :href="`tel:${phone.tel}`"
                                    class="font-medium text-white/75 transition-colors hover:text-white"
                                >
                                    {{ phone.display }}
                                </a>
                                <a
                                    :href="`https://wa.me/${phone.wa}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs font-semibold text-brand-orange transition-colors hover:text-brand-orangeDark"
                                >
                                    Chat on WhatsApp
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Social -->
                    <div>
                        <h3
                            class="text-sm font-bold uppercase tracking-wider text-white/90"
                        >
                            Follow
                        </h3>
                        <div class="mt-4 flex gap-3">
                            <a
                                href="https://instagram.com/Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Instagram"
                                class="rounded-full bg-white/10 p-2.5 text-white/80 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <Instagram class="h-5 w-5" />
                            </a>
                            <!-- Lucide has no TikTok glyph; inline brand SVG used. -->
                            <a
                                href="https://tiktok.com/@Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="TikTok"
                                class="rounded-full bg-white/10 p-2.5 text-white/80 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M16.5 3c.3 2.3 1.6 3.9 3.9 4.1v2.7c-1.4.1-2.7-.3-3.9-1v6.6c0 4.2-4.6 6.9-8.2 4.8-2.3-1.4-3-4.4-1.6-6.8 1-1.7 2.9-2.6 4.8-2.4v2.8c-.5-.1-1-.1-1.5.1-1.4.5-1.9 2.2-1 3.4.9 1.2 2.9.9 3.3-.6.07-.27.1-.55.1-.83V3h3.6z"
                                    />
                                </svg>
                            </a>
                            <a
                                href="https://facebook.com/Saturday_Emomotimi_Charles"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Facebook"
                                class="rounded-full bg-white/10 p-2.5 text-white/80 transition-colors hover:bg-brand-orange hover:text-white"
                            >
                                <Facebook class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-12 border-t border-white/10 pt-6 text-center text-xs text-white/50"
                >
                    &copy; {{ year }} BLOMFREE &amp; CO. NIG. LTD. All rights
                    reserved.
                </div>
            </div>
        </footer>

        <!-- Floating WhatsApp button -->
        <a
            :href="whatsappPrimary"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Chat with us on WhatsApp"
            class="fixed bottom-5 right-5 z-20 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/25 transition-transform duration-200 hover:scale-110 sm:bottom-6 sm:right-6"
        >
            <svg
                viewBox="0 0 24 24"
                fill="currentColor"
                class="h-7 w-7"
                aria-hidden="true"
            >
                <path
                    d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.42 1.3-1.95 1.34-.5.05-.96.24-3.23-.67-2.73-1.08-4.45-3.88-4.58-4.06-.13-.18-1.1-1.47-1.1-2.8 0-1.33.7-1.98.94-2.25.24-.27.53-.34.71-.34.18 0 .35 0 .51.01.16.01.39-.06.6.46.24.58.79 2 .86 2.14.07.14.12.31.02.49-.09.18-.14.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.54.15.27.66 1.09 1.42 1.77.97.87 1.79 1.14 2.05 1.27.26.13.41.11.56-.07.15-.18.65-.76.82-1.02.17-.26.35-.22.59-.13.24.09 1.51.71 1.77.84.26.13.43.2.49.31.06.11.06.66-.18 1.34z"
                />
            </svg>
        </a>

        <!-- Slide-in panels (mobile nav + cart drawer) -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="panelOpen"
                    class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
                    @click="closeAll"
                />
            </Transition>

            <!-- Mobile navigation -->
            <Transition name="slide">
                <aside
                    v-if="mobileOpen"
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xs flex-col bg-brand-dark text-white shadow-2xl"
                >
                    <div
                        class="flex h-16 items-center justify-between px-5 sm:h-20"
                    >
                        <span
                            class="text-lg font-extrabold tracking-tight"
                        >
                            BLOMFREE
                        </span>
                        <button
                            type="button"
                            class="rounded-full p-2 text-white/90 transition-colors hover:bg-white/10 hover:text-white"
                            aria-label="Close menu"
                            @click="mobileOpen = false"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <nav class="flex flex-col gap-1 px-3 py-4">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="rounded-xl px-4 py-3 text-base font-semibold transition-colors"
                            :class="
                                isActive(link.href)
                                    ? 'bg-white/10 text-brand-orange'
                                    : 'text-white/85 hover:bg-white/5 hover:text-white'
                            "
                            @click="mobileOpen = false"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>
                    <div class="mt-auto border-t border-white/10 p-5">
                        <a
                            :href="whatsappPrimary"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center justify-center rounded-full bg-brand-orange px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand-orangeDark"
                        >
                            Chat on WhatsApp
                        </a>
                    </div>
                </aside>
            </Transition>

            <!-- Cart drawer -->
            <Transition name="slide">
                <aside
                    v-if="cartOpen"
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white text-brand-dark shadow-2xl"
                >
                    <div
                        class="flex h-16 items-center justify-between border-b border-black/5 px-5 sm:h-20"
                    >
                        <div class="flex items-center gap-2">
                            <ShoppingCart class="h-5 w-5 text-brand-orange" />
                            <span class="text-base font-bold">Your Cart</span>
                        </div>
                        <button
                            type="button"
                            class="rounded-full p-2 text-brand-dark/70 transition-colors hover:bg-brand-cream hover:text-brand-dark"
                            aria-label="Close cart"
                            @click="cartOpen = false"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <!-- Cart body: items or empty state -->
                    <div
                        v-if="cart.items.length"
                        class="flex flex-1 flex-col overflow-y-auto"
                    >
                        <div
                            class="flex flex-1 flex-col divide-y divide-black/5 px-5"
                        >
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="py-4"
                            >
                                <CartLineItem :item="item" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center"
                    >
                        <ShoppingCart class="h-10 w-10 text-gray-300" />
                        <p class="text-sm font-medium text-gray-500">
                            Your cart is empty.
                        </p>
                        <p class="max-w-xs text-xs text-gray-400">
                            Add items from Collections, Gadgets or the Kennel
                            &amp; Farm to get started.
                        </p>
                    </div>

                    <!-- Footer: subtotal + actions -->
                    <div
                        v-if="cart.items.length"
                        class="border-t border-black/5 p-5"
                    >
                        <div
                            v-if="cart.has_animals"
                            class="mb-3 flex items-start gap-2 rounded-xl border border-brand-orange/30 bg-brand-orange/5 p-3 text-xs font-medium text-brand-dark"
                        >
                            <AlertTriangle
                                class="mt-0.5 h-4 w-4 shrink-0 text-brand-orange"
                            />
                            <span>
                                Live animal orders need special delivery —
                                our team will contact you within 24 hours
                                to confirm logistics.
                            </span>
                        </div>
                        <div
                            v-if="cart.has_issues"
                            class="mb-4 flex items-start gap-2 rounded-xl border border-yellow-300 bg-yellow-50 p-3 text-xs font-medium text-yellow-900"
                        >
                            <AlertTriangle
                                class="mt-0.5 h-4 w-4 shrink-0"
                            />
                            <span>
                                Some items need attention before checkout.
                            </span>
                        </div>
                        <div
                            class="flex items-baseline justify-between"
                        >
                            <span
                                class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55"
                            >
                                Subtotal
                            </span>
                            <span
                                class="text-lg font-extrabold text-brand-orange"
                            >
                                {{ formatNaira(cart.subtotal) }}
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-brand-dark/45">
                            Shipping calculated at checkout.
                        </p>
                        <div class="mt-4 flex flex-col gap-2">
                            <Button
                                v-if="cart.has_issues"
                                size="md"
                                disabled
                                class="w-full"
                            >
                                Continue to Checkout
                            </Button>
                            <Button
                                v-else
                                href="/checkout"
                                size="md"
                                class="w-full"
                                @click="cartOpen = false"
                            >
                                Continue to Checkout
                            </Button>
                            <Button
                                href="/cart"
                                variant="ghost"
                                size="sm"
                                class="w-full"
                                @click="cartOpen = false"
                            >
                                View full cart
                            </Button>
                        </div>
                    </div>
                </aside>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Truck,
    Store,
    AlertTriangle,
    Lock,
    ShieldCheck,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira, formatNigerianPhone } from '@/lib/format';
import { trackBeginCheckout } from '@/lib/analytics';
import type { CartData, CartLineItem } from '@/types/models';

interface ShippingZoneSummary {
    id: number;
    name: string;
    price: number;
    states: string[];
    delivery_estimate_days: string | null;
}

const props = defineProps<{
    cart: CartData;
    states: string[];
    shippingZones: ShippingZoneSummary[];
    pickupAddress: string | null;
    whatsappNumber: string;
}>();

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? null);

const showCartSummary = ref(false);

const form = useForm({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    delivery_method: 'delivery' as 'delivery' | 'pickup',
    delivery_state: '',
    delivery_lga: '',
    delivery_address: '',
    delivery_notes: '',
    payment_gateway: 'paystack' as 'paystack' | 'flutterwave',
});

const matchingZone = computed<ShippingZoneSummary | null>(() => {
    if (form.delivery_method !== 'delivery') return null;
    if (!form.delivery_state) return null;
    return (
        props.shippingZones.find((zone) =>
            zone.states.includes(form.delivery_state),
        ) ?? null
    );
});

const isPickup = computed(() => form.delivery_method === 'pickup');

const shippingFee = computed<number>(() => {
    if (isPickup.value) return 0;
    return matchingZone.value?.price ?? 0;
});

const total = computed(() => props.cart.subtotal + shippingFee.value);

const deliveryStateNotCovered = computed(
    () =>
        !isPickup.value && !!form.delivery_state && matchingZone.value === null,
);

const whatsappQuoteUrl = computed(() => {
    const text = `Hi BLOMFREE, I'd like a delivery quote to ${form.delivery_state} for an order on your site.`;
    return `https://wa.me/${props.whatsappNumber}?text=${encodeURIComponent(text)}`;
});

const canPlace = computed(() => {
    if (props.cart.has_issues) return false;
    if (!form.customer_name || !form.customer_email || !form.customer_phone) {
        return false;
    }
    if (!form.delivery_state || !form.delivery_lga || !form.delivery_address) {
        return false;
    }
    if (!isPickup.value && matchingZone.value === null) return false;

    return true;
});

// Pre-fill pickup details from the configured address so the model fields
// remain valid even on the pickup path.
watch(
    () => form.delivery_method,
    (method) => {
        if (method === 'pickup' && props.pickupAddress) {
            const lines = props.pickupAddress.split('\n');
            form.delivery_address = props.pickupAddress;
            form.delivery_state = form.delivery_state || 'Bayelsa';
            form.delivery_lga = form.delivery_lga || (lines[0] ?? 'Pickup');
        }
    },
);

function onPhoneInput(event: Event): void {
    form.customer_phone = formatNigerianPhone((event.target as HTMLInputElement).value);
}

onMounted(() => {
    trackBeginCheckout(
        props.cart.subtotal,
        props.cart.items.map((i) => ({
            id: i.id,
            name: i.name,
            price: i.price,
            quantity: i.quantity,
        })),
    );
});

function submit(): void {
    form.post('/checkout', {
        preserveScroll: true,
    });
}
</script>

<template>
    <SeoMeta title="Checkout — BLOMFREE & CO." />

    <AppLayout>
        <Section width="narrow">
            <Link
                href="/cart"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to cart
            </Link>

            <h1 class="mt-6 text-3xl font-extrabold tracking-tight sm:text-4xl">
                Checkout
            </h1>
            <p class="mt-2 text-sm text-brand-dark/55">
                Guest checkout — no account needed. We'll send a confirmation
                and tracking link to your email.
            </p>

            <div
                v-if="flashError"
                class="mt-5 flex items-start gap-2 rounded-2xl border border-red-300 bg-red-50 p-4 text-sm font-medium text-red-800"
            >
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ flashError }}</span>
            </div>

            <div
                v-if="cart.has_animals"
                class="mt-5 flex items-start gap-3 rounded-2xl border border-brand-orange/30 bg-brand-orange/5 p-4 text-sm font-medium text-brand-dark"
            >
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-brand-orange" />
                <span>
                    Your order includes a live animal. Live animals cannot
                    ship via regular courier — our team will contact you
                    within 24 hours to confirm delivery logistics.
                </span>
            </div>

            <!-- Section A: cart summary (collapsible) -->
            <div
                class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 text-left"
                    @click="showCartSummary = !showCartSummary"
                >
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                            Your cart
                        </p>
                        <p class="mt-0.5 font-bold tracking-tight">
                            {{ cart.count }}
                            {{ cart.count === 1 ? 'item' : 'items' }} —
                            {{ formatNaira(cart.subtotal) }}
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-brand-orange">
                        {{ showCartSummary ? 'Hide' : 'View' }}
                    </span>
                </button>
                <ul
                    v-if="showCartSummary"
                    class="mt-4 space-y-3 border-t border-black/5 pt-4"
                >
                    <li
                        v-for="item in cart.items as CartLineItem[]"
                        :key="item.id"
                        class="flex justify-between gap-3 text-sm"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium">{{ item.name }}</span>
                            <span
                                v-if="item.variant_label"
                                class="block truncate text-xs text-brand-dark/55"
                            >
                                {{ item.variant_label }} · qty {{ item.quantity }}
                            </span>
                            <span
                                v-else
                                class="block text-xs text-brand-dark/55"
                            >
                                qty {{ item.quantity }}
                            </span>
                        </span>
                        <span class="font-semibold text-brand-orange">
                            {{ formatNaira(item.line_total) }}
                        </span>
                    </li>
                </ul>
                <Link
                    href="/cart"
                    class="mt-3 inline-block text-xs font-semibold text-brand-orange hover:text-brand-orangeDark"
                >
                    Edit cart &rarr;
                </Link>
            </div>

            <!-- Section B: delivery details -->
            <form
                class="mt-6 space-y-6 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-8"
                @submit.prevent="submit"
            >
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight">
                        Delivery details
                    </h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="form-label" for="customer_name">Full name</label>
                            <input
                                id="customer_name"
                                v-model="form.customer_name"
                                type="text"
                                autocomplete="name"
                                class="form-input"
                            />
                            <p v-if="form.errors.customer_name" class="form-error">
                                {{ form.errors.customer_name }}
                            </p>
                        </div>
                        <div>
                            <label class="form-label" for="customer_email">Email</label>
                            <input
                                id="customer_email"
                                v-model="form.customer_email"
                                type="email"
                                autocomplete="email"
                                class="form-input"
                            />
                            <p v-if="form.errors.customer_email" class="form-error">
                                {{ form.errors.customer_email }}
                            </p>
                        </div>
                        <div>
                            <label class="form-label" for="customer_phone">Phone</label>
                            <input
                                id="customer_phone"
                                :value="form.customer_phone"
                                type="tel"
                                inputmode="tel"
                                placeholder="0801 234 5678"
                                autocomplete="tel"
                                class="form-input"
                                @input="onPhoneInput"
                            />
                            <p v-if="form.errors.customer_phone" class="form-error">
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="form-label">Delivery method</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-colors"
                                :class="
                                    form.delivery_method === 'delivery'
                                        ? 'border-brand-orange bg-brand-orange/5'
                                        : 'border-black/10 hover:border-black/20'
                                "
                            >
                                <input
                                    v-model="form.delivery_method"
                                    type="radio"
                                    value="delivery"
                                    class="mt-0.5 text-brand-orange focus:ring-brand-orange"
                                />
                                <span>
                                    <span class="flex items-center gap-1.5 font-bold">
                                        <Truck class="h-4 w-4" />
                                        Delivery to address
                                    </span>
                                    <span class="mt-0.5 block text-xs text-brand-dark/55">
                                        Charged per shipping zone.
                                    </span>
                                </span>
                            </label>
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-colors"
                                :class="
                                    form.delivery_method === 'pickup'
                                        ? 'border-brand-orange bg-brand-orange/5'
                                        : 'border-black/10 hover:border-black/20'
                                "
                            >
                                <input
                                    v-model="form.delivery_method"
                                    type="radio"
                                    value="pickup"
                                    class="mt-0.5 text-brand-orange focus:ring-brand-orange"
                                />
                                <span>
                                    <span class="flex items-center gap-1.5 font-bold">
                                        <Store class="h-4 w-4" />
                                        Pickup from store
                                    </span>
                                    <span class="mt-0.5 block text-xs text-brand-dark/55">
                                        No shipping fee.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Pickup address (read-only) -->
                    <div
                        v-if="isPickup && pickupAddress"
                        class="mt-4 rounded-2xl border-l-4 border-brand-orange bg-brand-cream p-4 text-sm"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                            Pickup address
                        </p>
                        <p class="mt-1 whitespace-pre-line font-medium">
                            {{ pickupAddress }}
                        </p>
                    </div>

                    <!-- Delivery address fields -->
                    <div
                        v-if="!isPickup"
                        class="mt-4 grid gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <label class="form-label" for="delivery_state">State</label>
                            <select
                                id="delivery_state"
                                v-model="form.delivery_state"
                                class="form-input"
                            >
                                <option value="" disabled>Select a state</option>
                                <option v-for="s in states" :key="s" :value="s">
                                    {{ s }}
                                </option>
                            </select>
                            <p v-if="form.errors.delivery_state" class="form-error">
                                {{ form.errors.delivery_state }}
                            </p>
                        </div>
                        <div>
                            <label class="form-label" for="delivery_lga">City / LGA</label>
                            <input
                                id="delivery_lga"
                                v-model="form.delivery_lga"
                                type="text"
                                class="form-input"
                            />
                            <p v-if="form.errors.delivery_lga" class="form-error">
                                {{ form.errors.delivery_lga }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label" for="delivery_address">Full address</label>
                            <textarea
                                id="delivery_address"
                                v-model="form.delivery_address"
                                rows="3"
                                class="form-input"
                            />
                            <p v-if="form.errors.delivery_address" class="form-error">
                                {{ form.errors.delivery_address }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label" for="delivery_notes">
                                Notes <span class="text-brand-dark/40">(optional)</span>
                            </label>
                            <input
                                id="delivery_notes"
                                v-model="form.delivery_notes"
                                type="text"
                                placeholder="Landmark, gate code, anything we should know"
                                class="form-input"
                            />
                        </div>
                    </div>

                    <!-- Shipping fee result / WhatsApp fallback -->
                    <div
                        v-if="!isPickup && matchingZone"
                        class="mt-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                    >
                        Shipping to {{ form.delivery_state }} —
                        <span class="font-bold">{{ formatNaira(matchingZone.price) }}</span>
                        <span
                            v-if="matchingZone.delivery_estimate_days"
                            class="font-normal"
                        >
                            ({{ matchingZone.delivery_estimate_days }})
                        </span>
                    </div>
                    <div
                        v-else-if="deliveryStateNotCovered"
                        class="mt-4 rounded-xl border border-yellow-300 bg-yellow-50 p-4 text-sm font-medium text-yellow-900"
                    >
                        <p class="flex items-start gap-2">
                            <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                            <span>
                                Contact us for a delivery quote — your state
                                is not yet in our delivery zones.
                            </span>
                        </p>
                        <a
                            :href="whatsappQuoteUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-2 inline-flex items-center rounded-full bg-[#25D366] px-4 py-2 text-xs font-semibold text-white"
                        >
                            Ask on WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Section C: order summary + gateway picker -->
                <div class="border-t border-black/5 pt-6">
                    <h2 class="text-lg font-extrabold tracking-tight">
                        Order summary
                    </h2>

                    <div class="mt-4 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-brand-dark/60">Subtotal</span>
                            <span class="font-semibold">
                                {{ formatNaira(cart.subtotal) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-brand-dark/60">Shipping</span>
                            <span class="font-semibold">
                                {{ shippingFee > 0 ? formatNaira(shippingFee) : '—' }}
                            </span>
                        </div>
                        <div class="mt-2 flex justify-between border-t border-black/5 pt-2 text-base">
                            <span class="font-bold">Total</span>
                            <span class="text-lg font-extrabold tracking-tight text-brand-orange">
                                {{ formatNaira(total) }}
                            </span>
                        </div>
                    </div>

                    <p class="mt-6 form-label">Payment method</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label
                            v-for="gw in [
                                { value: 'paystack', label: 'Paystack', desc: 'Card, transfer, USSD' },
                                { value: 'flutterwave', label: 'Flutterwave', desc: 'Card, transfer, bank' },
                            ]"
                            :key="gw.value"
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-colors"
                            :class="
                                form.payment_gateway === gw.value
                                    ? 'border-brand-orange bg-brand-orange/5'
                                    : 'border-black/10 hover:border-black/20'
                            "
                        >
                            <input
                                v-model="form.payment_gateway"
                                type="radio"
                                :value="gw.value"
                                class="mt-0.5 text-brand-orange focus:ring-brand-orange"
                            />
                            <span>
                                <span class="block font-bold">{{ gw.label }}</span>
                                <span class="mt-0.5 block text-xs text-brand-dark/55">
                                    {{ gw.desc }}
                                </span>
                            </span>
                        </label>
                    </div>
                    <p v-if="form.errors.payment_gateway" class="form-error">
                        {{ form.errors.payment_gateway }}
                    </p>
                </div>

                <!-- Section D: trust signals + place order -->
                <div class="border-t border-black/5 pt-6">
                    <div
                        class="mb-4 flex items-start gap-3 rounded-2xl bg-brand-cream p-4 text-xs text-brand-dark/75"
                    >
                        <ShieldCheck
                            class="mt-0.5 h-5 w-5 shrink-0 text-brand-orange"
                        />
                        <div>
                            <p class="font-bold text-brand-dark">
                                Secured by Paystack &amp; Flutterwave
                            </p>
                            <p class="mt-0.5">
                                Your payment is encrypted and processed by
                                Nigeria&rsquo;s trusted payment gateways. BLOMFREE
                                &amp; CO. never sees your card details.
                            </p>
                        </div>
                    </div>
                    <Button
                        type="submit"
                        size="lg"
                        class="w-full"
                        :disabled="!canPlace || form.processing"
                    >
                        <Lock class="h-4 w-4" />
                        {{
                            form.processing
                                ? 'Redirecting to payment…'
                                : `Place Order — ${formatNaira(total)}`
                        }}
                    </Button>
                    <p class="mt-3 text-center text-xs text-brand-dark/45">
                        You'll be redirected to your chosen payment gateway to
                        complete the order securely.
                    </p>
                </div>
            </form>
        </Section>
    </AppLayout>
</template>

<style scoped>
.form-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(26 26 26 / 0.6);
}
.form-input {
    width: 100%;
    border-radius: 0.75rem;
    border-color: rgb(26 26 26 / 0.15);
    font-size: 0.875rem;
}
.form-input:focus {
    border-color: #f58220;
    --tw-ring-color: #f58220;
}
.form-error {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #dc2626;
}
</style>

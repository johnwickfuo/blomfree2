<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock,
    Truck,
    Package,
    XCircle,
    AlertTriangle,
    MapPin,
    Mail,
    Phone,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira, formatDate } from '@/lib/format';
import { trackPurchase } from '@/lib/analytics';

interface OrderItemView {
    id: number;
    name: string;
    label: string | null;
    quantity: number;
    unit_price: number;
    subtotal: number;
    image: string | null;
}

interface OrderView {
    reference: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    delivery_address: string;
    delivery_state: string;
    delivery_lga: string | null;
    delivery_method: 'delivery' | 'pickup';
    shipping_fee: number;
    subtotal: number;
    total: number;
    payment_gateway: 'paystack' | 'flutterwave';
    payment_status: 'pending' | 'paid' | 'failed' | 'cancelled';
    order_status:
        | 'pending_payment'
        | 'paid'
        | 'processing'
        | 'shipped'
        | 'delivered'
        | 'cancelled'
        | 'refunded';
    tracking_notes: string | null;
    placed_at: string | null;
    paid_at: string | null;
    items: OrderItemView[];
}

const props = defineProps<{
    order: OrderView;
}>();

interface StatusStep {
    key: OrderView['order_status'];
    label: string;
    icon: typeof Clock;
}

const TIMELINE: StatusStep[] = [
    { key: 'pending_payment', label: 'Awaiting payment', icon: Clock },
    { key: 'paid', label: 'Payment received', icon: CheckCircle2 },
    { key: 'processing', label: 'Processing', icon: Package },
    { key: 'shipped', label: 'Shipped', icon: Truck },
    { key: 'delivered', label: 'Delivered', icon: CheckCircle2 },
];

const TIMELINE_ORDER: OrderView['order_status'][] = TIMELINE.map((s) => s.key);

const currentIndex = computed(() => {
    if (props.order.order_status === 'cancelled') return -2;
    if (props.order.order_status === 'refunded') return -3;
    return TIMELINE_ORDER.indexOf(props.order.order_status);
});

const isPaid = computed(() => props.order.payment_status === 'paid');
const isCancelled = computed(
    () =>
        props.order.order_status === 'cancelled' ||
        props.order.order_status === 'refunded',
);

const headlineMeta = computed(() => {
    if (props.order.order_status === 'cancelled') {
        return {
            icon: XCircle,
            iconClass: 'text-red-600',
            title: 'Order cancelled',
            subtitle: 'This order has been cancelled.',
        };
    }
    if (props.order.order_status === 'refunded') {
        return {
            icon: XCircle,
            iconClass: 'text-brand-dark/50',
            title: 'Order refunded',
            subtitle: 'This order has been refunded.',
        };
    }
    if (isPaid.value) {
        return {
            icon: CheckCircle2,
            iconClass: 'text-green-600',
            title: 'Order confirmed',
            subtitle: "We've sent a confirmation to your email.",
        };
    }
    return {
        icon: AlertTriangle,
        iconClass: 'text-yellow-600',
        title: 'Awaiting payment',
        subtitle: 'Complete payment to confirm this order.',
    };
});

const paymentBadge = computed<{
    label: string;
    variant: 'success' | 'warning' | 'neutral';
}>(() => {
    switch (props.order.payment_status) {
        case 'paid':
            return { label: 'Paid', variant: 'success' };
        case 'pending':
            return { label: 'Pending', variant: 'warning' };
        default:
            return { label: 'Cancelled', variant: 'neutral' };
    }
});

// Fire purchase event once per order reference so a refresh of the
// success page doesn't double-count revenue in GA / Facebook.
onMounted(() => {
    if (typeof window === 'undefined' || !isPaid.value) return;
    const key = 'blomfree.purchase.' + props.order.reference;
    try {
        if (window.sessionStorage.getItem(key)) return;
        window.sessionStorage.setItem(key, '1');
    } catch (_) {
        // Storage may be unavailable (private mode); fire anyway.
    }
    trackPurchase(
        props.order.reference,
        props.order.total,
        props.order.items.map((i) => ({
            id: i.id,
            name: i.name,
            price: i.unit_price,
            quantity: i.quantity,
        })),
    );
});
</script>

<template>
    <SeoMeta :title="'Order ' + order.reference + ' — BLOMFREE & CO.'" />

    <AppLayout>
        <Section width="narrow">
            <!-- Big check + reference -->
            <div class="text-center">
                <component
                    :is="headlineMeta.icon"
                    class="mx-auto h-16 w-16"
                    :class="headlineMeta.iconClass"
                />
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                    {{ headlineMeta.title }}
                </h1>
                <p class="mt-1 text-sm text-brand-dark/60">
                    {{ headlineMeta.subtitle }}
                </p>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-brand-dark/45">
                    Order reference
                </p>
                <p class="text-2xl font-extrabold tracking-tight text-brand-orange">
                    {{ order.reference }}
                </p>
                <p class="mt-3 text-xs text-brand-dark/45">
                    Bookmark this page to check your status any time — no
                    account required.
                </p>
            </div>

            <!-- Status timeline -->
            <div
                v-if="!isCancelled"
                class="mt-8 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-7"
            >
                <h2 class="text-base font-bold tracking-tight">Status</h2>
                <ol class="mt-4 space-y-3">
                    <li
                        v-for="(step, i) in TIMELINE"
                        :key="step.key"
                        class="flex items-center gap-3 text-sm"
                    >
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                            :class="
                                i <= currentIndex
                                    ? 'bg-brand-orange text-white'
                                    : 'bg-brand-dark/5 text-brand-dark/35'
                            "
                        >
                            <component :is="step.icon" class="h-4 w-4" />
                        </span>
                        <span
                            class="font-medium"
                            :class="
                                i <= currentIndex
                                    ? 'text-brand-dark'
                                    : 'text-brand-dark/40'
                            "
                        >
                            {{ step.label }}
                        </span>
                    </li>
                </ol>

                <div
                    v-if="order.order_status === 'shipped' && order.tracking_notes"
                    class="mt-5 rounded-2xl border-l-4 border-brand-orange bg-brand-cream p-4 text-sm"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                        Tracking
                    </p>
                    <p class="mt-1 whitespace-pre-line">{{ order.tracking_notes }}</p>
                </div>
            </div>

            <!-- Items + totals -->
            <div
                class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-7"
            >
                <h2 class="text-base font-bold tracking-tight">Items</h2>
                <ul class="mt-4 divide-y divide-black/5">
                    <li
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex gap-3 py-3"
                    >
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-brand-dark/5">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold">{{ item.name }}</p>
                            <p
                                v-if="item.label"
                                class="truncate text-xs text-brand-dark/55"
                            >
                                {{ item.label }}
                            </p>
                            <p class="text-xs text-brand-dark/55">
                                {{ formatNaira(item.unit_price) }} × {{ item.quantity }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold">
                            {{ formatNaira(item.subtotal) }}
                        </span>
                    </li>
                </ul>

                <dl
                    class="mt-4 space-y-1 border-t border-black/5 pt-4 text-sm"
                >
                    <div class="flex justify-between">
                        <dt class="text-brand-dark/60">Subtotal</dt>
                        <dd class="font-semibold">{{ formatNaira(order.subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-brand-dark/60">Shipping</dt>
                        <dd class="font-semibold">
                            {{ order.shipping_fee > 0 ? formatNaira(order.shipping_fee) : '—' }}
                        </dd>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-black/5 pt-2 text-base">
                        <dt class="font-bold">Total</dt>
                        <dd class="text-lg font-extrabold tracking-tight text-brand-orange">
                            {{ formatNaira(order.total) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Customer + delivery + payment -->
            <div
                class="mt-6 grid gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:grid-cols-2 sm:p-7"
            >
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                        Customer
                    </p>
                    <p class="mt-1 text-sm font-bold">{{ order.customer_name }}</p>
                    <p class="mt-0.5 flex items-center gap-1.5 text-sm text-brand-dark/65">
                        <Mail class="h-3.5 w-3.5" />
                        {{ order.customer_email }}
                    </p>
                    <p class="mt-0.5 flex items-center gap-1.5 text-sm text-brand-dark/65">
                        <Phone class="h-3.5 w-3.5" />
                        {{ order.customer_phone }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                        {{ order.delivery_method === 'pickup' ? 'Pickup' : 'Delivery' }}
                    </p>
                    <p class="mt-1 flex items-start gap-1.5 text-sm text-brand-dark/75">
                        <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <span class="whitespace-pre-line">
                            {{ order.delivery_address }}
                            <span v-if="order.delivery_lga" class="block">
                                {{ order.delivery_lga }}, {{ order.delivery_state }}
                            </span>
                            <span v-else class="block">{{ order.delivery_state }}</span>
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                        Payment
                    </p>
                    <p class="mt-1 text-sm font-medium capitalize">
                        {{ order.payment_gateway }}
                    </p>
                    <div class="mt-1">
                        <Badge :variant="paymentBadge.variant">
                            {{ paymentBadge.label }}
                        </Badge>
                    </div>
                    <p
                        v-if="order.paid_at"
                        class="mt-1 text-xs text-brand-dark/45"
                    >
                        Paid {{ formatDate(order.paid_at) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                        Placed
                    </p>
                    <p class="mt-1 text-sm text-brand-dark/75">
                        {{ formatDate(order.placed_at) }}
                    </p>
                </div>
            </div>

            <div class="mt-6 text-center">
                <Button href="/" variant="outline">Continue shopping</Button>
            </div>
        </Section>
    </AppLayout>
</template>

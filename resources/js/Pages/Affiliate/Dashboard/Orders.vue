<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { formatNaira } from '@/lib/format';

interface OrderRow {
    reference: string;
    placed_at: string | null;
    total: number;
    discount: number;
    commission: number;
    order_status: string;
    payment_status: string;
}

interface PaginatedOrders {
    data: OrderRow[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    orders: PaginatedOrders;
}>();

const formatDate = (iso: string | null): string =>
    iso ? new Date(iso).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
</script>

<template>
    <Head title="Referred orders" />

    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Referred orders</h1>
        <p class="mt-1 text-brand-dark/70">Every order placed with your code.</p>

        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
            <table class="w-full text-sm">
                <thead class="bg-brand-dark/5 text-left text-xs uppercase tracking-wider text-brand-dark/60">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Discount</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.reference" class="border-t border-black/5">
                        <td class="px-4 py-3 font-semibold">{{ o.reference }}</td>
                        <td class="px-4 py-3 text-brand-dark/70">{{ formatDate(o.placed_at) }}</td>
                        <td class="px-4 py-3 capitalize">{{ o.order_status.replace('_', ' ') }}</td>
                        <td class="px-4 py-3 text-right">{{ formatNaira(o.total) }}</td>
                        <td class="px-4 py-3 text-right">{{ formatNaira(o.discount) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-brand-orange">{{ formatNaira(o.commission) }}</td>
                    </tr>
                    <tr v-if="!orders.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-brand-dark/60">
                            No referred orders yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="orders.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in orders.links"
                :key="link.label"
                :href="link.url ?? '#'"
                v-html="link.label"
                class="rounded-md border border-black/10 px-3 py-1.5 text-sm"
                :class="{
                    'bg-brand-orange text-white': link.active,
                    'pointer-events-none text-brand-dark/30': !link.url,
                }"
            />
        </nav>
    </AccountLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { formatNaira } from '@/lib/format';

interface OrderRow {
    reference: string;
    placed_at: string | null;
    total: number;
    order_status: string;
    payment_status: string;
}

interface Paginated {
    data: OrderRow[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ orders: Paginated }>();

const formatDate = (iso: string | null): string => iso
    ? new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';
</script>

<template>
    <Head title="Order history" />
    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Order history</h1>
        <p class="mt-1 text-brand-dark/70">All orders we've received for this email.</p>

        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
            <table class="w-full text-sm">
                <thead class="bg-brand-dark/5 text-left text-xs uppercase tracking-wider text-brand-dark/60">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Placed</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.reference" class="border-t border-black/5">
                        <td class="px-4 py-3 font-semibold">
                            <Link :href="`/orders/${o.reference}`" class="text-brand-orange hover:underline">{{ o.reference }}</Link>
                        </td>
                        <td class="px-4 py-3 text-brand-dark/70">{{ formatDate(o.placed_at) }}</td>
                        <td class="px-4 py-3 capitalize">{{ o.order_status.replace('_', ' ') }}</td>
                        <td class="px-4 py-3 text-right">{{ formatNaira(o.total) }}</td>
                    </tr>
                    <tr v-if="!orders.data.length">
                        <td colspan="4" class="px-4 py-8 text-center text-brand-dark/60">No orders yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AccountLayout>
</template>

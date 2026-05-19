<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Withdrawal {
    reference: string;
    amount: number;
    fee: number;
    net_amount: number;
    status: string;
    requested_at: string | null;
    processed_at: string | null;
    payment_reference: string | null;
    admin_notes: string | null;
    bank_snapshot: { bank_name?: string; bank_account_number?: string; bank_account_name?: string } | null;
}

interface Paginated {
    data: Withdrawal[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    withdrawals: Paginated;
}>();

const formatDate = (iso: string | null): string =>
    iso ? new Date(iso).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

const statusColor = (s: string): string =>
    ({
        paid: 'bg-green-100 text-green-900',
        pending: 'bg-amber-100 text-amber-900',
        approved: 'bg-blue-100 text-blue-900',
        rejected: 'bg-red-100 text-red-900',
    }[s] ?? 'bg-gray-100');
</script>

<template>
    <Head title="Withdrawals" />

    <AffiliateLayout>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight">Withdrawals</h1>
            <Button href="/affiliate/dashboard/withdrawals/new">Request a withdrawal</Button>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
            <table class="w-full text-sm">
                <thead class="bg-brand-dark/5 text-left text-xs uppercase tracking-wider text-brand-dark/60">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Requested</th>
                        <th class="px-4 py-3">Bank</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="w in withdrawals.data" :key="w.reference" class="border-t border-black/5 align-top">
                        <td class="px-4 py-3 font-semibold">{{ w.reference }}</td>
                        <td class="px-4 py-3 text-brand-dark/70">{{ formatDate(w.requested_at) }}</td>
                        <td class="px-4 py-3 text-xs text-brand-dark/70">
                            <div>{{ w.bank_snapshot?.bank_name ?? '—' }}</div>
                            <div>{{ w.bank_snapshot?.bank_account_number ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize" :class="statusColor(w.status)">{{ w.status }}</span>
                            <div v-if="w.admin_notes" class="mt-1 text-xs text-brand-dark/60">{{ w.admin_notes }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">{{ formatNaira(w.amount) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ formatNaira(w.net_amount) }}</td>
                    </tr>
                    <tr v-if="!withdrawals.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-brand-dark/60">No withdrawals yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="withdrawals.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in withdrawals.links"
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
    </AffiliateLayout>
</template>

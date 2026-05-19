<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';
import { formatNaira } from '@/lib/format';

interface Commission {
    id: number;
    order_reference: string | null;
    amount: number;
    status: string;
    earned_at: string | null;
    available_at: string | null;
    reversed_at: string | null;
    reversal_reason: string | null;
}

interface Paginated {
    data: Commission[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    commissions: Paginated;
    filter: string | null;
}>();

const statuses = [
    { value: '', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'available', label: 'Available' },
    { value: 'withdrawn', label: 'Withdrawn' },
    { value: 'reversed', label: 'Reversed' },
];

const setFilter = (value: string): void => {
    router.get('/affiliate/dashboard/commissions', value ? { status: value } : {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

const formatDate = (iso: string | null): string =>
    iso ? new Date(iso).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

const statusColor = (s: string): string =>
    ({
        available: 'bg-green-100 text-green-900',
        pending: 'bg-amber-100 text-amber-900',
        reversed: 'bg-red-100 text-red-900',
        withdrawn: 'bg-gray-200 text-gray-700',
    }[s] ?? 'bg-gray-100');
</script>

<template>
    <Head title="Commissions" />

    <AffiliateLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Commissions</h1>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="s in statuses"
                :key="s.value"
                type="button"
                @click="setFilter(s.value)"
                class="rounded-full px-3 py-1.5 text-xs font-semibold"
                :class="(props.filter ?? '') === s.value ? 'bg-brand-orange text-white' : 'bg-brand-dark/5 text-brand-dark hover:bg-brand-dark/10'"
            >
                {{ s.label }}
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
            <table class="w-full text-sm">
                <thead class="bg-brand-dark/5 text-left text-xs uppercase tracking-wider text-brand-dark/60">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Earned</th>
                        <th class="px-4 py-3">Releases</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in commissions.data" :key="c.id" class="border-t border-black/5">
                        <td class="px-4 py-3 font-semibold">{{ c.order_reference ?? '—' }}</td>
                        <td class="px-4 py-3 text-brand-dark/70">{{ formatDate(c.earned_at) }}</td>
                        <td class="px-4 py-3 text-brand-dark/70">
                            <template v-if="c.status === 'pending'">{{ formatDate(c.available_at) }}</template>
                            <template v-else-if="c.status === 'reversed' && c.reversal_reason">
                                <span class="text-xs">{{ c.reversal_reason }}</span>
                            </template>
                            <template v-else>—</template>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize" :class="statusColor(c.status)">{{ c.status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ formatNaira(c.amount) }}</td>
                    </tr>
                    <tr v-if="!commissions.data.length">
                        <td colspan="5" class="px-4 py-8 text-center text-brand-dark/60">No commissions in this view.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="commissions.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in commissions.links"
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

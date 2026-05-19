<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { formatNaira } from '@/lib/format';

interface PlanRow {
    reference: string;
    installable_label: string;
    status: string;
    progress: number;
    amount_paid: number;
    total_amount: number;
    deadline: string | null;
}

interface Paginated {
    data: PlanRow[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ plans: Paginated; filter: string | null }>();

const statuses = [
    { value: '', label: 'All' },
    { value: 'pending_approval', label: 'Pending approval' },
    { value: 'awaiting_down_payment', label: 'Awaiting down payment' },
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'defaulted', label: 'Defaulted' },
    { value: 'refunded', label: 'Refunded' },
];

const setFilter = (v: string): void => {
    router.get('/account/installments', v ? { status: v } : {}, { preserveScroll: true, preserveState: true });
};

const formatDate = (iso: string | null): string => iso
    ? new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';

const statusColor = (s: string): string => ({
    active: 'bg-blue-100 text-blue-900',
    completed: 'bg-amber-100 text-amber-900',
    fulfilled: 'bg-green-100 text-green-900',
    awaiting_down_payment: 'bg-amber-100 text-amber-900',
    pending_approval: 'bg-amber-100 text-amber-900',
    defaulted: 'bg-red-100 text-red-900',
    cancelled_by_customer: 'bg-gray-100 text-gray-900',
    refunded: 'bg-gray-100 text-gray-900',
}[s] ?? 'bg-gray-100');
</script>

<template>
    <Head title="My installments" />
    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">My installments</h1>

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

        <div class="mt-6 space-y-3">
            <Link
                v-for="plan in plans.data"
                :key="plan.reference"
                :href="`/account/installments/${plan.reference}`"
                class="block rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-md"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-base font-bold">{{ plan.installable_label }}</p>
                        <p class="text-xs text-brand-dark/60">{{ plan.reference }}</p>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize" :class="statusColor(plan.status)">
                        {{ plan.status.replace('_', ' ') }}
                    </span>
                </div>
                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                    <div><span class="text-brand-dark/60">Progress:</span> <strong>{{ formatNaira(plan.amount_paid) }} / {{ formatNaira(plan.total_amount) }}</strong></div>
                    <div><span class="text-brand-dark/60">Deadline:</span> <strong>{{ formatDate(plan.deadline) }}</strong></div>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-brand-dark/10">
                    <div class="h-full bg-brand-orange" :style="{ width: plan.progress + '%' }"></div>
                </div>
            </Link>
            <p v-if="!plans.data.length" class="rounded-2xl border border-dashed border-black/10 bg-white/40 p-8 text-center text-sm text-brand-dark/60">
                No plans match this filter.
            </p>
        </div>

        <nav v-if="plans.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="link in plans.links"
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

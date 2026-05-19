<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Stats {
    active_count: number;
    lifetime_paid: number;
    outstanding: number;
    next_due: { plan_reference: string; due_date: string; amount: number } | null;
}

interface PlanRow {
    reference: string;
    installable_label: string;
    status: string;
    progress: number;
    amount_paid: number;
    total_amount: number;
    deadline: string | null;
}

defineProps<{ stats: Stats; recentPlans: PlanRow[] }>();

const flash = usePage().props.flash;

const formatDate = (iso: string | null): string => iso
    ? new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';
</script>

<template>
    <Head title="My account" />
    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Welcome back, {{ $page.props.auth.user.name }}</h1>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ flash.success }}</div>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Active installments</p>
                <p class="mt-2 text-2xl font-extrabold">{{ stats.active_count }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Total paid</p>
                <p class="mt-2 text-2xl font-extrabold">{{ formatNaira(stats.lifetime_paid) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Outstanding balance</p>
                <p class="mt-2 text-2xl font-extrabold">{{ formatNaira(stats.outstanding) }}</p>
            </div>
            <div class="rounded-2xl bg-brand-orange/10 p-5 shadow-sm ring-1 ring-brand-orange/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-orange">Next payment</p>
                <template v-if="stats.next_due">
                    <p class="mt-2 text-xl font-extrabold text-brand-orange">{{ formatNaira(stats.next_due.amount) }}</p>
                    <p class="text-xs text-brand-dark/70">{{ formatDate(stats.next_due.due_date) }} — {{ stats.next_due.plan_reference }}</p>
                </template>
                <p v-else class="mt-2 text-sm text-brand-dark/60">No upcoming payments.</p>
            </div>
        </section>

        <section class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold">Recent installment plans</h2>
                <Button href="/installments" variant="outline">Start a new plan</Button>
            </div>

            <div v-if="recentPlans.length" class="mt-4 space-y-3">
                <Link
                    v-for="plan in recentPlans"
                    :key="plan.reference"
                    :href="`/account/installments/${plan.reference}`"
                    class="block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold">{{ plan.installable_label }}</p>
                            <p class="text-xs text-brand-dark/60">{{ plan.reference }} · {{ plan.status.replace('_', ' ') }}</p>
                        </div>
                        <span class="text-sm font-bold text-brand-orange">{{ formatNaira(plan.amount_paid) }} / {{ formatNaira(plan.total_amount) }}</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-brand-dark/10">
                        <div class="h-full bg-brand-orange" :style="{ width: plan.progress + '%' }"></div>
                    </div>
                </Link>
            </div>
            <p v-else class="mt-4 rounded-2xl border border-dashed border-black/10 bg-white/40 p-8 text-center text-sm text-brand-dark/60">
                You have no installment plans yet. <Link href="/installments" class="font-semibold text-brand-orange hover:underline">Browse eligible items →</Link>
            </p>
        </section>
    </AccountLayout>
</template>

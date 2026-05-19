<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Copy, Check } from 'lucide-vue-next';
import AffiliateLayout from '@/Layouts/AffiliateLayout.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Affiliate {
    code: string;
    name: string;
    pending_balance: number;
    available_balance: number;
    total_earned: number;
    total_withdrawn: number;
    has_bank_details: boolean;
}

interface Commission {
    id: number;
    order_reference: string | null;
    amount: number;
    status: string;
    earned_at: string | null;
}

defineProps<{
    affiliate: Affiliate;
    recentCommissions: Commission[];
    totalReferrals: number;
    shareUrl: string;
}>();

const copied = ref(false);
const copy = async (text: string): Promise<void> => {
    await navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
};

const formatDate = (iso: string | null): string =>
    iso ? new Date(iso).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

const flash = usePage().props.flash;
</script>

<template>
    <Head title="Affiliate dashboard" />

    <AffiliateLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Welcome, {{ affiliate.name }}</h1>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-4 text-sm text-green-900">
            {{ flash.success }}
        </div>

        <section class="mt-6 rounded-2xl bg-brand-dark p-6 text-white">
            <p class="text-sm uppercase tracking-wider text-white/60">Your affiliate code</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <code class="rounded-lg bg-white/10 px-4 py-2 text-2xl font-bold">{{ affiliate.code }}</code>
                <button
                    type="button"
                    @click="copy(affiliate.code)"
                    class="inline-flex items-center gap-1 rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/20"
                >
                    <Check v-if="copied" class="h-4 w-4" />
                    <Copy v-else class="h-4 w-4" />
                    {{ copied ? 'Copied' : 'Copy' }}
                </button>
            </div>
            <p class="mt-3 text-sm text-white/70">
                Share this code on WhatsApp, Instagram, Twitter — wherever your audience is.
                Tell buyers to enter it at checkout to unlock the affiliate price.
            </p>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Available</p>
                <p class="mt-2 text-2xl font-extrabold text-brand-orange">{{ formatNaira(affiliate.available_balance) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Pending</p>
                <p class="mt-2 text-2xl font-extrabold">{{ formatNaira(affiliate.pending_balance) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Lifetime earned</p>
                <p class="mt-2 text-2xl font-extrabold">{{ formatNaira(affiliate.total_earned) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark/50">Referrals</p>
                <p class="mt-2 text-2xl font-extrabold">{{ totalReferrals }}</p>
            </div>
        </section>

        <div v-if="!affiliate.has_bank_details" class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm">
            <strong class="text-amber-900">Add your bank details</strong> to enable withdrawals.
            <Link href="/affiliate/dashboard/bank-details" class="ml-1 font-semibold text-brand-orange hover:underline">Add now →</Link>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <Button href="/affiliate/dashboard/withdrawals/new">Request a withdrawal</Button>
            <Button href="/affiliate/dashboard/commissions" variant="outline">See all commissions</Button>
        </div>

        <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Recent commissions</h2>
            <div v-if="recentCommissions.length" class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-brand-dark/50">
                        <tr>
                            <th class="pb-2">Order</th>
                            <th class="pb-2">Earned</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in recentCommissions" :key="c.id" class="border-t border-black/5">
                            <td class="py-2 font-semibold">{{ c.order_reference ?? '—' }}</td>
                            <td class="py-2 text-brand-dark/70">{{ formatDate(c.earned_at) }}</td>
                            <td class="py-2 capitalize">{{ c.status }}</td>
                            <td class="py-2 text-right font-semibold">{{ formatNaira(c.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="mt-3 text-sm text-brand-dark/60">
                No commissions yet. Share your code to start earning.
            </p>
        </section>
    </AffiliateLayout>
</template>

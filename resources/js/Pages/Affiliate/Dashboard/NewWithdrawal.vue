<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Affiliate {
    available_balance: number;
    has_bank_details: boolean;
}

const props = defineProps<{
    affiliate: Affiliate;
    minimum: number;
    fee: number;
    hasPending: boolean;
}>();

const form = useForm({
    amount: props.affiliate.available_balance >= props.minimum ? props.affiliate.available_balance : 0,
});

const netAmount = computed<number>(() => Math.max(0, Number(form.amount || 0) - props.fee));

const canSubmit = computed<boolean>(
    () =>
        props.affiliate.has_bank_details
        && !props.hasPending
        && Number(form.amount) >= props.minimum
        && Number(form.amount) <= props.affiliate.available_balance,
);

const submit = (): void => {
    form.post('/affiliate/dashboard/withdrawals');
};
</script>

<template>
    <Head title="Request a withdrawal" />

    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Request a withdrawal</h1>

        <div v-if="!affiliate.has_bank_details" class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm">
            <strong>Add your bank details first.</strong>
            <Link href="/affiliate/dashboard/bank-details" class="ml-1 font-semibold text-brand-orange hover:underline">Add now →</Link>
        </div>

        <div v-else-if="hasPending" class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm">
            You already have a withdrawal in progress. Wait for it to clear before requesting another.
        </div>

        <form v-else @submit.prevent="submit" class="mt-6 max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
            <div>
                <p class="text-sm text-brand-dark/70">Available balance</p>
                <p class="text-2xl font-extrabold text-brand-orange">{{ formatNaira(affiliate.available_balance) }}</p>
            </div>

            <label class="block text-sm font-semibold">
                Amount to withdraw
                <input
                    v-model.number="form.amount"
                    type="number"
                    :min="minimum"
                    :max="affiliate.available_balance"
                    step="100"
                    required
                    class="mt-1 w-full rounded-lg border border-black/15 px-3 py-2"
                />
            </label>

            <p v-if="form.errors.amount" class="text-xs text-red-600">{{ form.errors.amount }}</p>

            <div class="rounded-xl bg-brand-dark/5 p-4 text-sm">
                <div class="flex justify-between"><span>Amount</span><strong>{{ formatNaira(Number(form.amount || 0)) }}</strong></div>
                <div class="flex justify-between text-brand-dark/70"><span>Transfer fee</span><span>−{{ formatNaira(fee) }}</span></div>
                <div class="mt-2 flex justify-between border-t border-black/10 pt-2 text-base"><span>Net to your account</span><strong class="text-brand-orange">{{ formatNaira(netAmount) }}</strong></div>
            </div>

            <p class="text-xs text-brand-dark/60">
                Minimum withdrawal is {{ formatNaira(minimum) }}. Payouts are processed within 1–3 business days.
            </p>

            <Button type="submit" :disabled="!canSubmit || form.processing" size="lg">
                {{ form.processing ? 'Submitting…' : 'Submit request' }}
            </Button>
        </form>
    </AccountLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Download, FileText } from 'lucide-vue-next';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Payment {
    reference: string;
    amount: number;
    payment_gateway: string;
    payment_reference: string | null;
    payment_status: string;
    paid_at: string | null;
    is_down_payment: boolean;
}

interface ScheduleEntry {
    due_date: string;
    suggested_amount: number;
    label?: string;
    reminder_sent_at?: string;
}

interface Plan {
    reference: string;
    installable_label: string;
    subsidiary: string;
    status: string;
    total_amount: number;
    amount_paid: number;
    remaining: number;
    progress: number;
    minimum_down_payment_amount: number;
    down_payment_paid: boolean;
    deadline: string | null;
    forfeiture_percentage: number;
    activated_at: string | null;
    completed_at: string | null;
    cancelled_at: string | null;
    defaulted_at: string | null;
    payments: Payment[];
    suggested_schedule: ScheduleEntry[];
    admin_notes: string | null;
}

const props = defineProps<{ plan: Plan }>();

const flash = usePage().props.flash;
const showCancel = ref(false);

const paymentForm = useForm({
    amount: props.plan.down_payment_paid ? props.plan.remaining : props.plan.minimum_down_payment_amount,
    payment_gateway: 'paystack' as 'paystack' | 'flutterwave',
});

const minPaymentAmount = computed(() => props.plan.down_payment_paid ? 100 : props.plan.minimum_down_payment_amount);

const canPay = computed(() => ['awaiting_down_payment', 'active'].includes(props.plan.status));

const submitPayment = (): void => {
    paymentForm.post(`/installments/${props.plan.reference}/initiate-payment`);
};

const submitCancel = (): void => {
    router.post(`/account/installments/${props.plan.reference}/cancel`, {});
};

const formatDate = (iso: string | null): string => iso
    ? new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';

const daysToDeadline = computed<number | null>(() => {
    if (! props.plan.deadline) return null;
    const diff = new Date(props.plan.deadline).getTime() - Date.now();
    return Math.ceil(diff / 86400000);
});

const deadlineColor = computed(() => {
    const d = daysToDeadline.value;
    if (d === null) return 'text-brand-dark/60';
    if (d <= 7) return 'text-red-700';
    if (d <= 30) return 'text-amber-700';
    return 'text-green-700';
});
</script>

<template>
    <Head :title="plan.reference" />
    <AccountLayout>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase text-brand-dark/50">{{ plan.reference }}</p>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ plan.installable_label }}</h1>
                <span class="mt-1 inline-block rounded-full bg-brand-dark/10 px-2 py-0.5 text-xs font-semibold capitalize">{{ plan.status.replace('_', ' ') }}</span>
            </div>
            <a :href="`/account/installments/${plan.reference}/statement`" class="inline-flex items-center gap-1 rounded-lg bg-brand-dark/5 px-3 py-2 text-xs font-semibold hover:bg-brand-dark/10">
                <FileText class="h-4 w-4" /> Download statement
            </a>
        </div>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ flash.success }}</div>
        <div v-if="flash?.error" class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-900">{{ flash.error }}</div>

        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <div class="flex items-baseline justify-between">
                <span class="text-sm font-bold text-brand-dark/60">Progress</span>
                <span class="text-lg font-extrabold text-brand-orange">{{ formatNaira(plan.amount_paid) }} of {{ formatNaira(plan.total_amount) }}</span>
            </div>
            <div class="mt-2 h-3 overflow-hidden rounded-full bg-brand-dark/10">
                <div class="h-full bg-brand-orange transition-all" :style="{ width: plan.progress + '%' }"></div>
            </div>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                <div><span class="text-brand-dark/60">Remaining:</span> <strong>{{ formatNaira(plan.remaining) }}</strong></div>
                <div><span class="text-brand-dark/60">Deadline:</span> <strong :class="deadlineColor">{{ formatDate(plan.deadline) }} <span v-if="daysToDeadline !== null">({{ daysToDeadline }} days)</span></strong></div>
                <div><span class="text-brand-dark/60">Forfeiture if defaulted:</span> <strong>{{ plan.forfeiture_percentage }}%</strong></div>
            </div>
        </section>

        <section v-if="canPay" class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Make a payment</h2>
            <p v-if="!plan.down_payment_paid" class="mt-1 text-xs text-amber-700">
                Your first payment must be at least <strong>{{ formatNaira(plan.minimum_down_payment_amount) }}</strong> (the down payment) to activate this plan.
            </p>

            <form @submit.prevent="submitPayment" class="mt-3 grid gap-3 sm:grid-cols-[1fr,200px,auto]">
                <input v-model.number="paymentForm.amount" type="number" :min="minPaymentAmount" :max="plan.remaining" step="100" required class="rounded-lg border border-black/15 px-3 py-2 text-sm" />
                <select v-model="paymentForm.payment_gateway" class="rounded-lg border border-black/15 px-3 py-2 text-sm">
                    <option value="paystack">Paystack</option>
                    <option value="flutterwave">Flutterwave</option>
                </select>
                <Button type="submit" :disabled="paymentForm.processing">{{ paymentForm.processing ? 'Redirecting…' : 'Pay Now' }}</Button>
            </form>
            <p v-if="paymentForm.errors.amount" class="mt-2 text-xs text-red-600">{{ paymentForm.errors.amount }}</p>
            <p v-if="paymentForm.errors.payment_gateway" class="mt-2 text-xs text-red-600">{{ paymentForm.errors.payment_gateway }}</p>
        </section>

        <section v-if="plan.suggested_schedule.length" class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Suggested schedule</h2>
            <p class="mt-1 text-xs text-brand-dark/60">These dates are guidelines — only the overall deadline matters.</p>
            <table class="mt-3 w-full text-sm">
                <thead class="text-left text-xs uppercase text-brand-dark/50">
                    <tr><th class="pb-2">Due</th><th class="pb-2">Label</th><th class="pb-2 text-right">Suggested</th></tr>
                </thead>
                <tbody>
                    <tr v-for="(entry, i) in plan.suggested_schedule" :key="i" class="border-t border-black/5">
                        <td class="py-2">{{ formatDate(entry.due_date) }}</td>
                        <td class="py-2 text-brand-dark/70">{{ entry.label ?? '—' }}</td>
                        <td class="py-2 text-right font-semibold">{{ formatNaira(entry.suggested_amount) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Payment history</h2>
            <div v-if="plan.payments.length" class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-brand-dark/50">
                        <tr>
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Reference</th>
                            <th class="pb-2">Method</th>
                            <th class="pb-2 text-right">Amount</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in plan.payments" :key="p.reference" class="border-t border-black/5">
                            <td class="py-2">{{ formatDate(p.paid_at) }}</td>
                            <td class="py-2 font-semibold">{{ p.reference }}<span v-if="p.is_down_payment" class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-[10px] uppercase text-amber-900">Down</span></td>
                            <td class="py-2 capitalize">{{ p.payment_gateway }}</td>
                            <td class="py-2 text-right font-semibold">{{ formatNaira(p.amount) }}</td>
                            <td class="py-2 text-right">
                                <a :href="`/account/installments/${plan.reference}/payments/${p.reference}/receipt`" class="inline-flex items-center gap-1 text-xs text-brand-orange hover:underline">
                                    <Download class="h-3.5 w-3.5" /> Receipt
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="mt-3 text-sm text-brand-dark/60">No payments yet.</p>
        </section>

        <section v-if="plan.status === 'active'" class="mt-6 rounded-2xl border border-red-200 bg-red-50/30 p-5">
            <button v-if="!showCancel" type="button" @click="showCancel = true" class="text-sm font-semibold text-red-700 hover:underline">
                I want to cancel this plan
            </button>
            <div v-else>
                <h3 class="text-base font-bold text-red-900">Cancel this plan?</h3>
                <p class="mt-1 text-sm text-red-900">
                    Cancelling forfeits <strong>{{ plan.forfeiture_percentage }}%</strong> of what you've paid
                    ({{ formatNaira(plan.amount_paid * plan.forfeiture_percentage / 100) }}). The remaining
                    {{ formatNaira(plan.amount_paid * (100 - plan.forfeiture_percentage) / 100) }} will be refunded to your bank account on file. This cannot be undone.
                </p>
                <div class="mt-3 flex gap-3">
                    <button type="button" @click="submitCancel" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Yes, cancel and forfeit</button>
                    <button type="button" @click="showCancel = false" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand-dark ring-1 ring-black/15 hover:bg-brand-dark/5">Keep my plan</button>
                </div>
            </div>
        </section>
    </AccountLayout>
</template>

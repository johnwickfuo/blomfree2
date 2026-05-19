<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import { formatNaira } from '@/lib/format';

interface Installable {
    type: 'land' | 'product' | 'product_variant';
    id: number;
    label: string;
    sublabel: string | null;
    image: string | null;
    href: string | null;
}

interface Pricing {
    normal_price: number;
    price: number;
    affiliate_applied: boolean;
    down_payment_percentage: number;
    down_payment_amount: number;
    maximum_length_months: number;
    suggested_monthly: number;
    deadline_if_activated_today: string;
}

const props = defineProps<{
    installable: Installable;
    pricing: Pricing;
    affiliateCode: string | null;
    isLoggedIn: boolean;
    termsVersion: string;
    forfeiturePercentage: number;
}>();

const form = useForm({
    installable_type: props.installable.type,
    installable_id: props.installable.id,
    agreed_to_terms: false,
    request_notes: '',
    inspection_status: '',
    preferred_contact: 'email' as 'email' | 'phone' | 'whatsapp',
});

const submit = (): void => { form.post('/installments/initiate'); };

const formatDate = (iso: string): string =>
    new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Start an installment plan" />
    <AppLayout>
        <Section width="narrow">
            <Link :href="installable.href ?? '/installments'" class="text-sm font-semibold text-brand-dark/60 hover:text-brand-orange">← Back</Link>

            <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Start an installment plan</h1>

            <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div class="flex gap-4">
                    <img v-if="installable.image" :src="installable.image" :alt="installable.label" class="h-24 w-24 rounded-xl object-cover" />
                    <div class="flex-1">
                        <p class="text-base font-bold">{{ installable.label }}</p>
                        <p v-if="installable.sublabel" class="text-sm text-brand-dark/60">{{ installable.sublabel }}</p>
                    </div>
                </div>

                <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-brand-dark/60">Total amount</dt>
                        <dd class="font-bold">
                            <span v-if="pricing.affiliate_applied" class="mr-1 text-xs text-brand-dark/40 line-through">{{ formatNaira(pricing.normal_price) }}</span>
                            {{ formatNaira(pricing.price) }}
                            <span v-if="pricing.affiliate_applied" class="ml-1 rounded bg-brand-orange/10 px-1.5 py-0.5 text-[10px] uppercase text-brand-orange">Affiliate {{ affiliateCode }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-brand-dark/60">Down payment</dt>
                        <dd class="font-bold">{{ formatNaira(pricing.down_payment_amount) }} ({{ pricing.down_payment_percentage }}%)</dd>
                    </div>
                    <div>
                        <dt class="text-brand-dark/60">Maximum length</dt>
                        <dd class="font-bold">{{ pricing.maximum_length_months }} months</dd>
                    </div>
                    <div>
                        <dt class="text-brand-dark/60">Deadline if activated today</dt>
                        <dd class="font-bold">{{ formatDate(pricing.deadline_if_activated_today) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-brand-dark/60">Suggested monthly</dt>
                        <dd class="font-bold text-brand-orange">{{ formatNaira(pricing.suggested_monthly) }}</dd>
                    </div>
                </dl>

                <div class="mt-4 rounded-xl border border-red-200 bg-red-50/40 p-3 text-xs text-red-900">
                    <strong>{{ forfeiturePercentage }}% forfeiture clause:</strong> if you don't complete by the deadline, or cancel an active plan, {{ forfeiturePercentage }}% of what you've paid is forfeited and {{ 100 - forfeiturePercentage }}% is refunded.
                    <Link href="/installments/terms" target="_blank" class="ml-1 font-semibold underline">Full terms (v{{ termsVersion }})</Link>
                </div>
            </div>

            <div v-if="!isLoggedIn" class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm">
                You need an account to start an installment plan.
                <Link :href="`/login`" class="ml-1 font-semibold text-brand-orange hover:underline">Log in</Link>
                or
                <Link :href="`/register`" class="ml-1 font-semibold text-brand-orange hover:underline">create one</Link>.
            </div>

            <form v-else @submit.prevent="submit" class="mt-6 space-y-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div v-if="installable.type === 'land'" class="space-y-3 border-b border-black/10 pb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-brand-dark/60">For our team (lands only)</h3>
                    <textarea v-model="form.request_notes" placeholder="Why are you interested in this land? (optional)" rows="2" class="w-full rounded-lg border border-black/15 px-3 py-2 text-sm"></textarea>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select v-model="form.inspection_status" class="rounded-lg border border-black/15 px-3 py-2 text-sm">
                            <option value="">Have you inspected this land?</option>
                            <option value="yes">Yes, I've inspected</option>
                            <option value="no">No, not yet</option>
                            <option value="want_schedule">Would like to schedule one</option>
                        </select>
                        <select v-model="form.preferred_contact" class="rounded-lg border border-black/15 px-3 py-2 text-sm">
                            <option value="email">Contact me by email</option>
                            <option value="phone">Contact me by phone</option>
                            <option value="whatsapp">Contact me by WhatsApp</option>
                        </select>
                    </div>
                </div>

                <label class="flex items-start gap-2 text-sm">
                    <input v-model="form.agreed_to_terms" type="checkbox" class="mt-1" />
                    <span>I have read and agree to the
                        <Link href="/installments/terms" target="_blank" class="font-semibold text-brand-orange hover:underline">installment terms (v{{ termsVersion }})</Link>,
                        including the {{ forfeiturePercentage }}% forfeiture clause.
                    </span>
                </label>
                <p v-if="form.errors.agreed_to_terms" class="-mt-2 text-xs text-red-600">{{ form.errors.agreed_to_terms }}</p>
                <p v-if="form.errors.installable_id" class="text-xs text-red-600">{{ form.errors.installable_id }}</p>

                <Button type="submit" :disabled="form.processing" size="lg">
                    {{ form.processing ? 'Submitting…' : installable.type === 'land' ? 'Request installment plan' : 'Continue to down payment' }}
                </Button>
            </form>
        </Section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Search, AlertTriangle } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';

defineProps<{
    order: { reference: string } | null;
    lookup?: { reference?: string; email?: string };
    notFound?: boolean;
}>();

const form = useForm({
    reference: '',
    email: '',
});

function submit(): void {
    form.post('/track', {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Track Your Order" />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                Track Your Order
            </h1>
            <p class="mt-2 text-sm text-brand-dark/60">
                Enter your order reference and the email you used at checkout.
            </p>

            <form
                class="mt-6 space-y-4 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-7"
                @submit.prevent="submit"
            >
                <div>
                    <label class="form-label" for="reference">Order reference</label>
                    <input
                        id="reference"
                        v-model="form.reference"
                        type="text"
                        placeholder="BLM-XXXXXX"
                        class="form-input"
                    />
                    <p v-if="form.errors.reference" class="form-error">
                        {{ form.errors.reference }}
                    </p>
                </div>
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-input"
                    />
                    <p v-if="form.errors.email" class="form-error">
                        {{ form.errors.email }}
                    </p>
                </div>

                <Button type="submit" size="lg" class="w-full" :disabled="form.processing">
                    <Search class="h-4 w-4" />
                    {{ form.processing ? 'Looking up…' : 'Find Order' }}
                </Button>
            </form>

            <div
                v-if="notFound"
                class="mt-5 flex items-start gap-2 rounded-2xl border border-yellow-300 bg-yellow-50 p-4 text-sm font-medium text-yellow-900"
            >
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>
                    We could not find an order matching that reference and
                    email. Please double-check both fields.
                </span>
            </div>

            <div v-if="order" class="mt-6 text-center">
                <Link
                    :href="'/orders/' + order.reference"
                    class="text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                >
                    View order details &rarr;
                </Link>
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.form-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(26 26 26 / 0.6);
}
.form-input {
    width: 100%;
    border-radius: 0.75rem;
    border-color: rgb(26 26 26 / 0.15);
    font-size: 0.875rem;
}
.form-input:focus {
    border-color: #f58220;
    --tw-ring-color: #f58220;
}
.form-error {
    margin-top: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #dc2626;
}
</style>

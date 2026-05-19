<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, Wallet, ShieldAlert, FileCheck } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';

defineProps<{ forfeiturePercentage: number }>();
</script>

<template>
    <Head title="Buy What You Need. Pay Over Time." />
    <SeoMeta title="BLOMFREE Installments" description="Spread the cost of a plot of land or a gadget over several months. Pay flexibly until the deadline." />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">
                Buy What You Need. Pay Over Time.
            </h1>
            <p class="mt-4 text-lg text-brand-dark/70">
                Spread the cost of <strong>land</strong> or a <strong>gadget</strong> over several months. Pay a down payment to lock it in, then pay flexibly until your deadline.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <Button href="/lands" size="lg">Browse Lands</Button>
                <Button href="/gadgets" size="lg" variant="outline">Browse Gadgets</Button>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <CheckCircle class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">1. Pick an eligible item</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">Lands and gadgets marked "Installment Available" can be paid for over time.</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <Wallet class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">2. Pay your down payment</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">Each item sets its own minimum down payment %. Once received, your plan activates.</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <FileCheck class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">3. Pay flexibly</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">Pay any amount any time until your deadline. Early completion is welcome and has no penalty.</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <ShieldAlert class="h-6 w-6 text-brand-orange" />
                    <h3 class="mt-3 text-lg font-bold">4. Receive your item</h3>
                    <p class="mt-1 text-sm text-brand-dark/70">Once fully paid we ship your gadget or transfer your land documents.</p>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-red-200 bg-red-50/40 p-5 text-sm text-red-900">
                <strong>{{ forfeiturePercentage }}% forfeiture clause:</strong> if you miss your deadline or cancel an active plan, {{ forfeiturePercentage }}% of what you've paid is retained and the remaining {{ 100 - forfeiturePercentage }}% is refunded to your bank account.
                <Link href="/installments/terms" class="ml-1 font-semibold underline">Read the full terms →</Link>
            </div>

            <div class="mt-8">
                <p class="text-sm text-brand-dark/60">
                    Not yet a customer? <Link href="/register" class="font-semibold text-brand-orange hover:underline">Create your account</Link>.
                </p>
            </div>
        </Section>
    </AppLayout>
</template>

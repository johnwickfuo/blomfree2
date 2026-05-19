<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
</script>

<template>
    <Head title="Application received" />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight">Application received</h1>
            <p class="mt-4 text-brand-dark/70">
                Thanks for applying! Your affiliate account is awaiting approval. We'll email you as soon as it's active —
                usually within one business day.
            </p>
            <div class="mt-6">
                <Link href="/" class="font-semibold text-brand-orange hover:underline">Back to home</Link>
            </div>
        </Section>
    </AppLayout>
</template>

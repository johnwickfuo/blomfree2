<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';

defineProps<{
    reason: string | null;
}>();
</script>

<template>
    <Head title="Account suspended" />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight">Your affiliate account is suspended</h1>
            <p class="mt-4 text-brand-dark/70">
                While suspended your code won't work at checkout and you can't request withdrawals.
                Existing balances are retained.
            </p>
            <div v-if="reason" class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-900">
                <strong>Reason from admin:</strong> {{ reason }}
            </div>
            <p class="mt-6 text-sm text-brand-dark/70">
                Think this is a mistake? Email
                <a href="mailto:admin@blomfree.com" class="font-semibold text-brand-orange hover:underline">admin@blomfree.com</a>.
            </p>
            <div class="mt-6">
                <Link href="/" class="font-semibold text-brand-orange hover:underline">Back to home</Link>
            </div>
        </Section>
    </AppLayout>
</template>

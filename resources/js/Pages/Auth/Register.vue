<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.post('/register', { onSuccess: () => form.reset('password', 'password_confirmation') });
};
</script>

<template>
    <Head title="Create account" />
    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight">Create your account</h1>
            <p class="mt-2 text-brand-dark/70">
                Already have one? <Link href="/login" class="font-semibold text-brand-orange hover:underline">Log in</Link>.
            </p>

            <form @submit.prevent="submit" class="mt-8 max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div>
                    <label class="block text-sm font-semibold">Full name</label>
                    <input v-model="form.name" type="text" required class="input mt-1" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold">Email</label>
                    <input v-model="form.email" type="email" required class="input mt-1" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold">Phone <span class="text-xs text-brand-dark/50">(optional)</span></label>
                    <input v-model="form.phone" type="tel" placeholder="0810 123 4567" class="input mt-1" />
                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold">Password</label>
                    <input v-model="form.password" type="password" required class="input mt-1" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold">Confirm password</label>
                    <input v-model="form.password_confirmation" type="password" required class="input mt-1" />
                </div>

                <Button type="submit" :disabled="form.processing" size="lg">
                    {{ form.processing ? 'Creating account…' : 'Create account' }}
                </Button>
            </form>
        </Section>
    </AppLayout>
</template>

<style scoped>
.input {
    @apply w-full rounded-lg border border-black/15 bg-white px-3 py-2 text-sm focus:border-brand-orange focus:outline-none focus:ring-2 focus:ring-brand-orange/30;
}
</style>

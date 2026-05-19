<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    whatsapp_number: '',
    password: '',
    password_confirmation: '',
    social_handles: {
        instagram: '',
        twitter: '',
        tiktok: '',
        facebook: '',
    },
    agreed_to_terms: false,
});

const submit = (): void => {
    form.post('/affiliate/signup');
};
</script>

<template>
    <Head title="Affiliate Signup — BLOMFREE" />
    <SeoMeta title="Become a BLOMFREE Affiliate" />

    <AppLayout>
        <Section width="narrow">
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Create your affiliate account</h1>
            <p class="mt-2 text-brand-dark/70">
                Already an affiliate?
                <Link href="/login" class="font-semibold text-brand-orange hover:underline">Log in</Link>.
            </p>

            <form @submit.prevent="submit" class="mt-8 space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="grid gap-4 sm:grid-cols-2">
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
                        <label class="block text-sm font-semibold">Phone</label>
                        <input v-model="form.phone" type="tel" required placeholder="0810 123 4567" class="input mt-1" />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">WhatsApp (optional)</label>
                        <input v-model="form.whatsapp_number" type="tel" class="input mt-1" />
                        <p v-if="form.errors.whatsapp_number" class="mt-1 text-xs text-red-600">{{ form.errors.whatsapp_number }}</p>
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
                </div>

                <fieldset class="rounded-xl bg-brand-dark/5 p-4">
                    <legend class="px-2 text-sm font-semibold">Social handles (optional)</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input v-model="form.social_handles.instagram" placeholder="Instagram handle" class="input" />
                        <input v-model="form.social_handles.twitter" placeholder="Twitter / X handle" class="input" />
                        <input v-model="form.social_handles.tiktok" placeholder="TikTok handle" class="input" />
                        <input v-model="form.social_handles.facebook" placeholder="Facebook handle" class="input" />
                    </div>
                </fieldset>

                <label class="flex items-start gap-2 text-sm">
                    <input v-model="form.agreed_to_terms" type="checkbox" class="mt-1" />
                    <span>
                        I have read and agree to the
                        <Link href="/affiliate/terms" class="font-semibold text-brand-orange hover:underline">Affiliate Terms</Link>.
                    </span>
                </label>
                <p v-if="form.errors.agreed_to_terms" class="-mt-2 text-xs text-red-600">{{ form.errors.agreed_to_terms }}</p>

                <Button type="submit" :disabled="form.processing" size="lg">
                    {{ form.processing ? 'Creating account…' : 'Create my affiliate code' }}
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

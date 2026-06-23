<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';

defineProps<{
    user: { name: string; email: string; phone: string | null };
}>();

const form = useForm({
    whatsapp_number: '',
    social_handles: {
        instagram: '',
        twitter: '',
        tiktok: '',
        facebook: '',
    },
    agreed_to_terms: false,
});

const submit = (): void => {
    form.post('/account/affiliate/become');
};
</script>

<template>
    <Head title="Become an Affiliate" />

    <AccountLayout>
        <div class="max-w-2xl">
            <span
                class="inline-flex items-center gap-1 rounded-full bg-brand-orange/10 px-3 py-1 text-xs font-semibold text-brand-orange"
            >
                <Sparkles class="h-3.5 w-3.5" />
                Affiliate Program
            </span>

            <h1 class="mt-3 text-3xl font-extrabold tracking-tight">
                Join the BLOMFREE affiliate program
            </h1>
            <p class="mt-3 text-brand-dark/70">
                You're already signed in as
                <span class="font-semibold">{{ user.email }}</span> — we just
                need a few more details to generate your code. Share it on WhatsApp,
                Instagram or with friends and earn commission on every eligible sale.
            </p>

            <p class="mt-4 text-sm text-brand-dark/60">
                Read the
                <Link
                    href="/affiliate/terms"
                    class="font-semibold text-brand-orange hover:underline"
                    >Affiliate Terms</Link
                >
                before you join.
            </p>

            <form
                @submit.prevent="submit"
                class="mt-8 space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5"
            >
                <div>
                    <label class="block text-sm font-semibold">
                        WhatsApp number (optional)
                    </label>
                    <input
                        v-model="form.whatsapp_number"
                        type="tel"
                        :placeholder="user.phone ?? '0810 123 4567'"
                        class="input mt-1"
                    />
                    <p
                        v-if="form.errors.whatsapp_number"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.whatsapp_number }}
                    </p>
                    <p class="mt-1 text-xs text-brand-dark/55">
                        Defaults to your phone number on file.
                    </p>
                </div>

                <fieldset class="rounded-xl bg-brand-dark/5 p-4">
                    <legend class="px-2 text-sm font-semibold">
                        Social handles (optional)
                    </legend>
                    <p class="mb-3 text-xs text-brand-dark/60">
                        Help us understand where you'll share your code.
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input
                            v-model="form.social_handles.instagram"
                            placeholder="Instagram handle"
                            class="input"
                        />
                        <input
                            v-model="form.social_handles.twitter"
                            placeholder="Twitter / X handle"
                            class="input"
                        />
                        <input
                            v-model="form.social_handles.tiktok"
                            placeholder="TikTok handle"
                            class="input"
                        />
                        <input
                            v-model="form.social_handles.facebook"
                            placeholder="Facebook handle"
                            class="input"
                        />
                    </div>
                </fieldset>

                <label class="flex items-start gap-2 text-sm">
                    <input
                        v-model="form.agreed_to_terms"
                        type="checkbox"
                        class="mt-1"
                    />
                    <span>
                        I have read and agree to the
                        <Link
                            href="/affiliate/terms"
                            class="font-semibold text-brand-orange hover:underline"
                            >Affiliate Terms</Link
                        >.
                    </span>
                </label>
                <p
                    v-if="form.errors.agreed_to_terms"
                    class="-mt-2 text-xs text-red-600"
                >
                    {{ form.errors.agreed_to_terms }}
                </p>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    size="lg"
                >
                    {{
                        form.processing
                            ? 'Activating…'
                            : 'Activate my affiliate code'
                    }}
                </Button>
            </form>
        </div>
    </AccountLayout>
</template>

<style scoped>
.input {
    @apply w-full rounded-lg border border-black/15 bg-white px-3 py-2 text-sm focus:border-brand-orange focus:outline-none focus:ring-2 focus:ring-brand-orange/30;
}
</style>

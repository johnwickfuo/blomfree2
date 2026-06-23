<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';

interface Affiliate {
    name: string;
    email: string;
    phone: string | null;
    whatsapp_number: string | null;
    social_handles: Record<string, string> | null;
}

const props = defineProps<{ affiliate: Affiliate }>();

const form = useForm({
    whatsapp_number: props.affiliate.whatsapp_number ?? '',
    social_handles: {
        instagram: props.affiliate.social_handles?.instagram ?? '',
        twitter: props.affiliate.social_handles?.twitter ?? '',
        tiktok: props.affiliate.social_handles?.tiktok ?? '',
        facebook: props.affiliate.social_handles?.facebook ?? '',
    },
});

const submit = (): void => {
    form.patch('/affiliate/dashboard/profile');
};

const flash = usePage().props.flash;
</script>

<template>
    <Head title="Profile" />

    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Profile</h1>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ flash.success }}</div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h2 class="text-lg font-bold">Account</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-brand-dark/60">Name</dt><dd class="font-semibold">{{ affiliate.name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-brand-dark/60">Email</dt><dd class="font-semibold">{{ affiliate.email }}</dd></div>
                    <div class="flex justify-between"><dt class="text-brand-dark/60">Phone</dt><dd class="font-semibold">{{ affiliate.phone ?? '—' }}</dd></div>
                </dl>
            </div>

            <form @submit.prevent="submit" class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h2 class="text-lg font-bold">Public details</h2>

                <div>
                    <label class="block text-sm font-semibold">WhatsApp</label>
                    <input v-model="form.whatsapp_number" type="tel" class="mt-1 w-full rounded-lg border border-black/15 px-3 py-2" />
                    <p v-if="form.errors.whatsapp_number" class="mt-1 text-xs text-red-600">{{ form.errors.whatsapp_number }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <input v-model="form.social_handles.instagram" placeholder="Instagram handle" class="rounded-lg border border-black/15 px-3 py-2 text-sm" />
                    <input v-model="form.social_handles.twitter" placeholder="Twitter / X handle" class="rounded-lg border border-black/15 px-3 py-2 text-sm" />
                    <input v-model="form.social_handles.tiktok" placeholder="TikTok handle" class="rounded-lg border border-black/15 px-3 py-2 text-sm" />
                    <input v-model="form.social_handles.facebook" placeholder="Facebook handle" class="rounded-lg border border-black/15 px-3 py-2 text-sm" />
                </div>

                <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save profile' }}</Button>
            </form>
        </div>
    </AccountLayout>
</template>

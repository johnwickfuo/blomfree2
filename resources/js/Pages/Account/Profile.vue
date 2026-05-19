<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';

interface UserDto {
    name: string;
    email: string;
    phone: string | null;
    delivery_address: string | null;
    delivery_state: string | null;
    delivery_lga: string | null;
}

const props = defineProps<{ user: UserDto }>();

const profile = useForm({
    name: props.user.name,
    phone: props.user.phone ?? '',
    delivery_address: props.user.delivery_address ?? '',
    delivery_state: props.user.delivery_state ?? '',
    delivery_lga: props.user.delivery_lga ?? '',
});

const pwForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({ password: '' });

const showDelete = ref(false);

const submitProfile = (): void => { profile.patch('/account/profile'); };
const submitPassword = (): void => { pwForm.put('/account/password', { onSuccess: () => pwForm.reset() }); };
const submitDelete = (): void => { deleteForm.delete('/account/account'); };

const flash = usePage().props.flash;
</script>

<template>
    <Head title="Profile & security" />
    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Profile & security</h1>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ flash.success }}</div>

        <form @submit.prevent="submitProfile" class="mt-6 space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Profile</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold">Full name</label>
                    <input v-model="profile.name" type="text" required class="input mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-semibold">Email <span class="text-xs text-brand-dark/50">(read-only)</span></label>
                    <input :value="user.email" type="email" readonly class="input mt-1 bg-brand-dark/5" />
                </div>
                <div>
                    <label class="block text-sm font-semibold">Phone</label>
                    <input v-model="profile.phone" type="tel" placeholder="0810 123 4567" class="input mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-semibold">Delivery state</label>
                    <input v-model="profile.delivery_state" type="text" class="input mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-semibold">Delivery LGA</label>
                    <input v-model="profile.delivery_lga" type="text" class="input mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold">Delivery address</label>
                    <textarea v-model="profile.delivery_address" rows="3" class="input mt-1"></textarea>
                </div>
            </div>
            <Button type="submit" :disabled="profile.processing">{{ profile.processing ? 'Saving…' : 'Save profile' }}</Button>
        </form>

        <form @submit.prevent="submitPassword" class="mt-6 space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
            <h2 class="text-lg font-bold">Change password</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <input v-model="pwForm.current_password" type="password" placeholder="Current password" required class="input" />
                <input v-model="pwForm.password" type="password" placeholder="New password" required class="input" />
                <input v-model="pwForm.password_confirmation" type="password" placeholder="Confirm new" required class="input" />
            </div>
            <p v-if="pwForm.errors.current_password" class="text-xs text-red-600">{{ pwForm.errors.current_password }}</p>
            <p v-if="pwForm.errors.password" class="text-xs text-red-600">{{ pwForm.errors.password }}</p>
            <Button type="submit" :disabled="pwForm.processing" variant="outline">{{ pwForm.processing ? 'Updating…' : 'Update password' }}</Button>
        </form>

        <section class="mt-6 rounded-2xl border border-red-200 bg-red-50/30 p-6">
            <h2 class="text-lg font-bold text-red-900">Deactivate account</h2>
            <p class="mt-1 text-sm text-red-900/80">
                Your account will be soft-deleted and you'll be logged out. You won't be able to log in again. Active installment plans must be completed or cancelled first.
            </p>
            <button v-if="!showDelete" type="button" @click="showDelete = true" class="mt-3 text-sm font-semibold text-red-700 hover:underline">
                I want to deactivate
            </button>
            <form v-else @submit.prevent="submitDelete" class="mt-3 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold">Confirm password</label>
                    <input v-model="deleteForm.password" type="password" required class="input mt-1" />
                    <p v-if="deleteForm.errors.password" class="mt-1 text-xs text-red-700">{{ deleteForm.errors.password }}</p>
                </div>
                <button type="submit" :disabled="deleteForm.processing" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    {{ deleteForm.processing ? 'Deactivating…' : 'Deactivate' }}
                </button>
                <button type="button" @click="showDelete = false" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand-dark ring-1 ring-black/15">Cancel</button>
            </form>
        </section>
    </AccountLayout>
</template>

<style scoped>
.input {
    @apply w-full rounded-lg border border-black/15 bg-white px-3 py-2 text-sm focus:border-brand-orange focus:outline-none focus:ring-2 focus:ring-brand-orange/30;
}
</style>

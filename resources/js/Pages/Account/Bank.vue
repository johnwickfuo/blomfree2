<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AccountLayout from '@/Layouts/AccountLayout.vue';
import Button from '@/Components/Button.vue';

interface UserDto {
    bank_name: string | null;
    bank_account_number: string | null;
    bank_account_name: string | null;
}

const props = defineProps<{ user: UserDto }>();

const form = useForm({
    bank_name: props.user.bank_name ?? '',
    bank_account_number: props.user.bank_account_number ?? '',
    bank_account_name: props.user.bank_account_name ?? '',
});

const submit = (): void => { form.patch('/account/bank'); };
const flash = usePage().props.flash;
</script>

<template>
    <Head title="Bank details" />
    <AccountLayout>
        <h1 class="text-2xl font-extrabold tracking-tight">Bank details</h1>
        <p class="mt-1 text-brand-dark/70">Used for receiving installment refunds if a plan defaults or is cancelled.</p>

        <div v-if="flash?.success" class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ flash.success }}</div>

        <form @submit.prevent="submit" class="mt-6 max-w-lg space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
            <div>
                <label class="block text-sm font-semibold">Bank name</label>
                <input v-model="form.bank_name" type="text" required class="mt-1 w-full rounded-lg border border-black/15 px-3 py-2" placeholder="e.g. GTBank" />
                <p v-if="form.errors.bank_name" class="mt-1 text-xs text-red-600">{{ form.errors.bank_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold">Account number</label>
                <input v-model="form.bank_account_number" type="text" required pattern="\d{10}" class="mt-1 w-full rounded-lg border border-black/15 px-3 py-2" placeholder="10 digits" />
                <p v-if="form.errors.bank_account_number" class="mt-1 text-xs text-red-600">{{ form.errors.bank_account_number }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold">Account name</label>
                <input v-model="form.bank_account_name" type="text" required class="mt-1 w-full rounded-lg border border-black/15 px-3 py-2" placeholder="Exact name on the account" />
                <p v-if="form.errors.bank_account_name" class="mt-1 text-xs text-red-600">{{ form.errors.bank_account_name }}</p>
            </div>

            <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save bank details' }}</Button>
        </form>
    </AccountLayout>
</template>

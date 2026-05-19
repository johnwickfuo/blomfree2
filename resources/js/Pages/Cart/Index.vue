<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ShoppingCart, AlertTriangle, Tag, X } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import CartLineItem from '@/Components/CartLineItem.vue';
import { formatNaira } from '@/lib/format';
import type { CartData } from '@/types/models';

const page = usePage();
const cart = computed<CartData>(() => page.props.cart);

const showCodeInput = ref(false);
const codeForm = useForm({ code: '' });

watch(
    () => cart.value.affiliate,
    (a) => {
        if (a) {
            showCodeInput.value = false;
            codeForm.code = '';
        }
    },
);

const applyCode = (): void => {
    codeForm.post('/cart/apply-affiliate', { preserveScroll: true });
};

const removeCode = (): void => {
    router.delete('/cart/apply-affiliate', { preserveScroll: true });
};
</script>

<template>
    <Head title="Your Cart" />

    <AppLayout>
        <Section width="narrow">
            <Link
                href="/"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Continue shopping
            </Link>

            <h1
                class="mt-6 text-3xl font-extrabold tracking-tight sm:text-4xl"
            >
                Your Cart
            </h1>

            <div v-if="cart.items.length" class="mt-8 grid gap-6">
                <div class="space-y-3">
                    <div
                        v-for="item in cart.items"
                        :key="item.id"
                        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5"
                    >
                        <CartLineItem :item="item" />
                    </div>
                </div>

                <div
                    v-if="cart.has_animals"
                    class="flex items-start gap-3 rounded-2xl border border-brand-orange/30 bg-brand-orange/5 p-4 text-sm font-medium text-brand-dark"
                >
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-brand-orange" />
                    <span>
                        Live animal orders require special delivery
                        arrangements. Our team will contact you within 24
                        hours to confirm logistics.
                    </span>
                </div>

                <div
                    v-if="cart.has_issues"
                    class="flex items-start gap-3 rounded-2xl border border-yellow-300 bg-yellow-50 p-4 text-sm font-medium text-yellow-900"
                >
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0" />
                    <span>
                        Some items in your cart need attention. Adjust the
                        quantity or remove them before continuing to checkout.
                    </span>
                </div>

                <div
                    class="flex flex-col gap-3 rounded-3xl bg-white p-6 shadow-md ring-1 ring-black/5 sm:p-7"
                >
                    <div
                        v-if="cart.affiliate"
                        class="flex items-center justify-between gap-3 rounded-xl bg-brand-orange/10 px-4 py-3 text-sm"
                    >
                        <div>
                            <Tag class="mr-1 inline h-4 w-4 text-brand-orange" />
                            Affiliate code <strong>{{ cart.affiliate.code }}</strong>
                            applied — you saved
                            <strong>{{ formatNaira(cart.affiliate.discount_total) }}</strong>.
                        </div>
                        <button
                            type="button"
                            @click="removeCode"
                            class="text-brand-orange hover:text-brand-orangeDark"
                            aria-label="Remove affiliate code"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div v-else>
                        <button
                            v-if="!showCodeInput"
                            type="button"
                            @click="showCodeInput = true"
                            class="text-sm font-semibold text-brand-orange hover:underline"
                        >
                            Have an affiliate code?
                        </button>

                        <form v-else @submit.prevent="applyCode" class="flex gap-2">
                            <input
                                v-model="codeForm.code"
                                type="text"
                                placeholder="BLM-XXXXXX"
                                required
                                class="flex-1 rounded-lg border border-black/15 px-3 py-2 text-sm uppercase"
                            />
                            <Button type="submit" :disabled="codeForm.processing">Apply</Button>
                        </form>
                        <p v-if="codeForm.errors.code" class="mt-1 text-xs text-red-600">{{ codeForm.errors.code }}</p>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-sm font-semibold uppercase tracking-wide text-brand-dark/55"
                        >
                            Subtotal
                        </span>
                        <span
                            class="text-2xl font-extrabold tracking-tight text-brand-orange"
                        >
                            {{ formatNaira(cart.subtotal) }}
                        </span>
                    </div>
                    <p class="text-xs text-brand-dark/45">
                        Shipping and total are calculated at checkout.
                    </p>
                    <Button
                        v-if="cart.has_issues"
                        size="lg"
                        disabled
                        class="mt-2"
                    >
                        Continue to Checkout
                    </Button>
                    <Button
                        v-else
                        href="/checkout"
                        size="lg"
                        class="mt-2"
                    >
                        Continue to Checkout
                    </Button>
                </div>
            </div>

            <div
                v-else
                class="mt-8 flex flex-col items-center gap-3 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-20 text-center"
            >
                <ShoppingCart class="h-10 w-10 text-brand-dark/25" />
                <p class="text-lg font-bold tracking-tight">
                    Your cart is empty
                </p>
                <p class="max-w-sm text-sm text-brand-dark/55">
                    Nothing here yet — explore Collections, Gadgets, or the
                    Kennel & Farm to add your first item.
                </p>
                <Link
                    href="/"
                    class="mt-1 text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                >
                    Start shopping &rarr;
                </Link>
            </div>
        </Section>
    </AppLayout>
</template>

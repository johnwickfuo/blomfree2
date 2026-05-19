<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Minus, Plus, Trash2, AlertCircle } from 'lucide-vue-next';
import { formatNaira } from '@/lib/format';
import type { CartLineItem as CartLineItemType } from '@/types/models';

const props = defineProps<{
    item: CartLineItemType;
}>();

const canIncrement = computed(
    () => props.item.quantity < props.item.max_quantity,
);
const canDecrement = computed(() => props.item.quantity > 1);

function setQuantity(quantity: number): void {
    if (quantity < 1) return;
    router.patch(
        `/cart/items/${props.item.id}`,
        { quantity },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['cart'],
        },
    );
}

function remove(): void {
    router.delete(`/cart/items/${props.item.id}`, {
        preserveScroll: true,
        preserveState: true,
        only: ['cart'],
    });
}
</script>

<template>
    <div class="flex gap-4">
        <component
            :is="item.href ? Link : 'div'"
            :href="item.href ?? undefined"
            class="block h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-brand-dark/5"
        >
            <img
                v-if="item.image"
                :src="item.image"
                :alt="item.name"
                class="h-full w-full object-cover"
            />
        </component>

        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
            <component
                :is="item.href ? Link : 'p'"
                :href="item.href ?? undefined"
                class="truncate text-sm font-bold tracking-tight transition-colors"
                :class="item.href ? 'hover:text-brand-orange' : ''"
            >
                {{ item.name }}
            </component>

            <p
                v-if="item.variant_label"
                class="truncate text-xs text-brand-dark/55"
            >
                {{ item.variant_label }}
            </p>

            <p class="text-sm font-semibold text-brand-orange">
                {{ formatNaira(item.price) }}
                <span
                    v-if="item.quantity > 1"
                    class="text-xs font-medium text-brand-dark/55"
                >
                    × {{ item.quantity }} ={{ ' ' }}
                    {{ formatNaira(item.line_total) }}
                </span>
            </p>

            <p
                v-if="item.issue"
                class="flex items-start gap-1.5 text-xs font-medium text-red-600"
            >
                <AlertCircle class="mt-px h-3.5 w-3.5 shrink-0" />
                {{ item.issue }}
            </p>

            <div class="mt-1 flex items-center justify-between gap-2">
                <div
                    class="flex items-center rounded-lg border border-brand-dark/10 bg-white"
                >
                    <button
                        type="button"
                        :disabled="!canDecrement"
                        class="flex h-8 w-8 items-center justify-center text-brand-dark/60 transition-colors hover:text-brand-dark disabled:opacity-30"
                        aria-label="Decrease quantity"
                        @click="setQuantity(item.quantity - 1)"
                    >
                        <Minus class="h-3.5 w-3.5" />
                    </button>
                    <span class="w-8 text-center text-xs font-bold">
                        {{ item.quantity }}
                    </span>
                    <button
                        type="button"
                        :disabled="!canIncrement"
                        class="flex h-8 w-8 items-center justify-center text-brand-dark/60 transition-colors hover:text-brand-dark disabled:opacity-30"
                        aria-label="Increase quantity"
                        @click="setQuantity(item.quantity + 1)"
                    >
                        <Plus class="h-3.5 w-3.5" />
                    </button>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-brand-dark/40 transition-colors hover:bg-red-50 hover:text-red-600"
                    aria-label="Remove item"
                    @click="remove"
                >
                    <Trash2 class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>

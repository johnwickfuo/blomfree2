<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { formatNaira } from '@/lib/format';
import { colorToHex } from '@/lib/colors';
import type { ProductCardData, Subsidiary } from '@/types/models';

const props = defineProps<{
    product: ProductCardData;
    subsidiary: Subsidiary;
}>();

const href = computed(() => `/${props.subsidiary}/${props.product.slug}`);

const hasDiscount = computed(
    () =>
        props.product.compare_price !== null &&
        parseFloat(props.product.compare_price) > props.product.display_price,
);

const swatches = computed(() =>
    props.product.colors.slice(0, 5).map((name) => ({
        name,
        hex: colorToHex(name),
    })),
);
</script>

<template>
    <Card class="flex flex-col overflow-hidden">
        <Link :href="href" class="block">
            <div class="aspect-square w-full bg-brand-dark/5">
                <img
                    v-if="product.cover_url"
                    :src="product.cover_url"
                    :alt="product.name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover"
                />
            </div>
        </Link>

        <div class="flex flex-1 flex-col gap-2 p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/45">
                    {{ product.category }}
                </p>
                <Badge :variant="product.is_in_stock ? 'success' : 'neutral'">
                    {{ product.is_in_stock ? 'In Stock' : 'Out of Stock' }}
                </Badge>
            </div>

            <Link
                :href="href"
                class="font-bold leading-snug tracking-tight transition-colors hover:text-brand-orange"
            >
                {{ product.name }}
            </Link>

            <div
                v-if="swatches.length"
                class="flex items-center gap-1.5"
                aria-label="Available colours"
            >
                <span
                    v-for="swatch in swatches"
                    :key="swatch.name"
                    :title="swatch.name"
                    class="h-4 w-4 rounded-full ring-1 ring-black/10"
                    :class="swatch.hex === null ? 'bg-brand-dark/20' : ''"
                    :style="swatch.hex ? { backgroundColor: swatch.hex } : undefined"
                />
            </div>

            <div class="mt-auto flex items-baseline gap-2 pt-2">
                <span class="text-lg font-extrabold tracking-tight text-brand-orange">
                    {{ formatNaira(product.display_price) }}
                </span>
                <span
                    v-if="hasDiscount"
                    class="text-sm font-medium text-brand-dark/40 line-through"
                >
                    {{ formatNaira(product.compare_price) }}
                </span>
            </div>
        </div>
    </Card>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronLeft, Minus, Plus, ShoppingCart, Check } from 'lucide-vue-next';
import { openCartDrawer } from '@/lib/cart';
import { trackViewItem, trackAddToCart } from '@/lib/analytics';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import Lightbox from '@/Components/Lightbox.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import JsonLd from '@/Components/JsonLd.vue';
import { formatNaira } from '@/lib/format';
import { colorToHex } from '@/lib/colors';
import type {
    ProductCardData,
    ProductDetailData,
    ProductVariantData,
    Subsidiary,
} from '@/types/models';

interface InstallmentSummary {
    enabled: boolean;
    down_payment_percentage: number;
    maximum_length_months: number;
    requires_variant_selection: boolean;
    initiate_url: string;
}

const props = defineProps<{
    subsidiary: Subsidiary;
    product: ProductDetailData;
    variants: ProductVariantData[];
    optionValues: Record<string, string[]>;
    related: ProductCardData[];
    installment?: InstallmentSummary | null;
}>();

const WHATSAPP_NUMBER = '2348103965317';

const gallery = computed<string[]>(() => props.product.gallery_urls ?? []);
const lightboxIndex = ref<number | null>(null);

const addingToCart = ref(false);
const quantity = ref(1);

// One selection slot per attribute key (variant products only).
const selected = reactive<Record<string, string | null>>(
    Object.fromEntries(props.product.attribute_keys.map((key) => [key, null])),
);

function isOptionAvailable(key: string, value: string): boolean {
    return props.variants.some((variant) => {
        if (variant.attributes[key] !== value) {
            return false;
        }

        return props.product.attribute_keys.every((otherKey) => {
            if (otherKey === key) {
                return true;
            }
            const current = selected[otherKey];
            return current === null || variant.attributes[otherKey] === current;
        });
    });
}

function selectOption(key: string, value: string): void {
    if (!isOptionAvailable(key, value)) {
        return;
    }

    selected[key] = value;

    // Drop any other selections that are no longer consistent with this pick.
    for (const otherKey of props.product.attribute_keys) {
        const current = selected[otherKey];
        if (
            otherKey !== key &&
            current !== null &&
            !isOptionAvailable(otherKey, current)
        ) {
            selected[otherKey] = null;
        }
    }
}

const allKeysSelected = computed(() =>
    props.product.attribute_keys.every((key) => selected[key] !== null),
);

const matchedVariant = computed<ProductVariantData | null>(() => {
    if (!props.product.has_variants || !allKeysSelected.value) {
        return null;
    }

    return (
        props.variants.find((variant) =>
            props.product.attribute_keys.every(
                (key) => variant.attributes[key] === selected[key],
            ),
        ) ?? null
    );
});

const combinationUnavailable = computed(
    () =>
        props.product.has_variants &&
        allKeysSelected.value &&
        matchedVariant.value === null,
);

const currentPrice = computed<number>(() => {
    if (props.product.has_variants && matchedVariant.value) {
        return matchedVariant.value.price;
    }
    return props.product.display_price;
});

const currentStock = computed<number | null>(() => {
    if (props.product.has_variants) {
        return matchedVariant.value?.stock ?? null;
    }
    return props.product.stock;
});

const maxQuantity = computed<number>(() => {
    if (props.product.has_variants) {
        return matchedVariant.value?.stock ?? 0;
    }
    return props.product.stock ?? 0;
});

const hasDiscount = computed(
    () =>
        props.product.compare_price !== null &&
        parseFloat(props.product.compare_price) > currentPrice.value,
);

const canAddToCart = computed(() => {
    if (quantity.value < 1 || quantity.value > maxQuantity.value) {
        return false;
    }

    if (props.product.has_variants) {
        return matchedVariant.value !== null && matchedVariant.value.in_stock;
    }

    return props.product.is_in_stock;
});

watch(maxQuantity, (max) => {
    if (quantity.value > max) {
        quantity.value = Math.max(1, max);
    }
});

function decrement(): void {
    if (quantity.value > 1) {
        quantity.value -= 1;
    }
}

function increment(): void {
    if (quantity.value < maxQuantity.value) {
        quantity.value += 1;
    }
}

function addToCart(): void {
    if (!canAddToCart.value || addingToCart.value) return;

    const payload = props.product.has_variants
        ? {
            cartable_type: 'product_variant',
            cartable_id: matchedVariant.value!.id,
            quantity: quantity.value,
        }
        : {
            cartable_type: 'product',
            cartable_id: props.product.id,
            quantity: quantity.value,
        };

    addingToCart.value = true;
    router.post('/cart/add', payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            trackAddToCart({
                id: props.product.id,
                name: props.product.name,
                price: currentPrice.value,
                quantity: quantity.value,
                category: props.product.category,
            });
            openCartDrawer();
        },
        onFinish: () => {
            addingToCart.value = false;
        },
    });
}

// "Order via WhatsApp" fallback — pre-fills the product name and page link.
const pageUrl = ref('');
onMounted(() => {
    pageUrl.value = window.location.href;

    trackViewItem({
        id: props.product.id,
        name: props.product.name,
        price: props.product.display_price,
        category: props.product.category,
    });
});

const productSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: props.product.name,
    description: props.product.short_description,
    category: props.product.category,
    brand: { '@type': 'Organization', name: 'BLOMFREE & CO.' },
    image: props.product.gallery_urls?.[0] ?? props.product.cover_url ?? undefined,
    offers: {
        '@type': 'Offer',
        price: props.product.display_price,
        priceCurrency: 'NGN',
        availability: props.product.is_in_stock
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
    },
}));

const whatsappOrderUrl = computed(() => {
    const text = `Hi BLOMFREE, I'd like to order: ${props.product.name}.\n${pageUrl.value}`;
    return `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(text)}`;
});

const specs = computed(() => {
    const rows: { label: string; value: string }[] = [
        { label: 'Category', value: props.product.category },
        {
            label: 'Subsidiary',
            value: props.subsidiary === 'collections' ? 'Collections' : 'Gadgets',
        },
        {
            label: 'Type',
            value: props.product.has_variants
                ? `Configurable — ${props.product.attribute_keys.join(', ')}`
                : 'Single item',
        },
    ];

    if (props.product.has_variants && matchedVariant.value) {
        rows.push({ label: 'Selected', value: matchedVariant.value.label });
        if (matchedVariant.value.sku) {
            rows.push({ label: 'SKU', value: matchedVariant.value.sku });
        }
    }

    if (!props.product.has_variants && props.product.stock !== null) {
        rows.push({ label: 'In stock', value: `${props.product.stock} unit(s)` });
    }

    return rows;
});

function isColorKey(key: string): boolean {
    return key.toLowerCase() === 'color';
}
</script>

<template>
    <SeoMeta
        :title="product.name + ' — BLOMFREE ' + (subsidiary === 'collections' ? 'Collections' : 'Gadgets')"
        :description="product.short_description"
        :image="product.cover_url ?? undefined"
    />
    <JsonLd :schema="productSchema" />

    <AppLayout>
        <Section>
            <Link
                :href="`/${subsidiary}`"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to {{ subsidiary === 'collections' ? 'Collections' : 'Gadgets' }}
            </Link>

            <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:gap-12">
                <!-- Gallery -->
                <div>
                    <div class="overflow-hidden rounded-3xl">
                        <button
                            v-if="gallery.length"
                            type="button"
                            class="block aspect-square w-full bg-brand-dark/5"
                            @click="lightboxIndex = 0"
                        >
                            <img
                                :src="gallery[0]"
                                :alt="product.name"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                            />
                        </button>
                        <div
                            v-else
                            class="flex aspect-square w-full items-center justify-center bg-brand-dark/5 text-sm text-brand-dark/40"
                        >
                            No images available
                        </div>
                    </div>
                    <div
                        v-if="gallery.length > 1"
                        class="mt-3 grid grid-cols-4 gap-3"
                    >
                        <button
                            v-for="(image, i) in gallery.slice(1)"
                            :key="i"
                            type="button"
                            class="aspect-square overflow-hidden rounded-xl bg-brand-dark/5 ring-1 ring-black/5 transition-opacity hover:opacity-80"
                            @click="lightboxIndex = i + 1"
                        >
                            <img :src="image" alt="" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/45">
                        {{ product.category }}
                    </p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        {{ product.name }}
                    </h1>

                    <!-- Price -->
                    <div class="mt-4 flex items-baseline gap-3">
                        <span class="text-3xl font-extrabold tracking-tight text-brand-orange">
                            {{ formatNaira(currentPrice) }}
                        </span>
                        <span
                            v-if="hasDiscount"
                            class="text-lg font-medium text-brand-dark/40 line-through"
                        >
                            {{ formatNaira(product.compare_price) }}
                        </span>
                    </div>

                    <p class="mt-3 text-base text-brand-dark/65">
                        {{ product.short_description }}
                    </p>

                    <!-- Variant selectors -->
                    <div v-if="product.has_variants" class="mt-7 space-y-5">
                        <div
                            v-for="key in product.attribute_keys"
                            :key="key"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55">
                                {{ key }}
                                <span
                                    v-if="selected[key]"
                                    class="ml-1 normal-case text-brand-dark/80"
                                >
                                    — {{ selected[key] }}
                                </span>
                            </p>

                            <!-- Colour swatches -->
                            <div
                                v-if="isColorKey(key)"
                                class="mt-2 flex flex-wrap gap-3"
                            >
                                <button
                                    v-for="value in optionValues[key]"
                                    :key="value"
                                    type="button"
                                    :disabled="!isOptionAvailable(key, value)"
                                    class="flex flex-col items-center gap-1 transition-opacity disabled:opacity-30"
                                    @click="selectOption(key, value)"
                                >
                                    <span
                                        class="h-9 w-9 rounded-full ring-2 ring-offset-2 transition-all"
                                        :class="[
                                            selected[key] === value
                                                ? 'ring-brand-orange'
                                                : 'ring-black/10',
                                            colorToHex(value) === null
                                                ? 'bg-brand-dark/20'
                                                : '',
                                        ]"
                                        :style="
                                            colorToHex(value)
                                                ? { backgroundColor: colorToHex(value) as string }
                                                : undefined
                                        "
                                    />
                                    <span class="text-xs text-brand-dark/55">
                                        {{ value }}
                                    </span>
                                </button>
                            </div>

                            <!-- Buttons (size, storage, etc.) -->
                            <div v-else class="mt-2 flex flex-wrap gap-2">
                                <button
                                    v-for="value in optionValues[key]"
                                    :key="value"
                                    type="button"
                                    :disabled="!isOptionAvailable(key, value)"
                                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-30"
                                    :class="
                                        selected[key] === value
                                            ? 'border-brand-orange bg-brand-orange text-white'
                                            : 'border-brand-dark/15 bg-white text-brand-dark/75 hover:border-brand-dark/30'
                                    "
                                    @click="selectOption(key, value)"
                                >
                                    {{ value }}
                                </button>
                            </div>
                        </div>

                        <!-- Variant status -->
                        <p
                            v-if="combinationUnavailable"
                            class="rounded-xl bg-brand-dark/5 px-4 py-2.5 text-sm font-medium text-brand-dark/60"
                        >
                            Combination unavailable — try a different option.
                        </p>
                        <p
                            v-else-if="matchedVariant"
                            class="text-sm font-medium"
                            :class="matchedVariant.in_stock ? 'text-green-700' : 'text-brand-dark/50'"
                        >
                            {{
                                matchedVariant.in_stock
                                    ? `In stock — ${matchedVariant.stock} available`
                                    : 'This variant is out of stock'
                            }}
                        </p>
                    </div>

                    <!-- Stock line for non-variant products -->
                    <p
                        v-else
                        class="mt-6 text-sm font-medium"
                        :class="product.is_in_stock ? 'text-green-700' : 'text-brand-dark/50'"
                    >
                        {{
                            product.is_in_stock
                                ? `In stock — ${currentStock} available`
                                : 'Out of stock'
                        }}
                    </p>

                    <!-- Quantity + Add to Cart -->
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <div
                            class="flex items-center rounded-xl border border-brand-dark/15 bg-white"
                        >
                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center text-brand-dark/60 transition-colors hover:text-brand-dark disabled:opacity-30"
                                :disabled="quantity <= 1"
                                aria-label="Decrease quantity"
                                @click="decrement"
                            >
                                <Minus class="h-4 w-4" />
                            </button>
                            <span class="w-10 text-center text-sm font-bold">
                                {{ quantity }}
                            </span>
                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center text-brand-dark/60 transition-colors hover:text-brand-dark disabled:opacity-30"
                                :disabled="quantity >= maxQuantity"
                                aria-label="Increase quantity"
                                @click="increment"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>

                        <Button
                            size="lg"
                            :disabled="!canAddToCart || addingToCart"
                            @click="addToCart"
                        >
                            <ShoppingCart class="h-4 w-4" />
                            {{ addingToCart ? 'Adding…' : 'Add to Cart' }}
                        </Button>
                    </div>

                    <!-- Installment CTA (gadgets only, when installment_enabled) -->
                    <div v-if="installment" class="mt-3 rounded-xl bg-brand-orange/10 p-4 text-sm">
                        <p>
                            <strong class="text-brand-orange">Pay in installments:</strong>
                            {{ installment.down_payment_percentage }}% down, up to {{ installment.maximum_length_months }} months. 10% forfeiture if not completed.
                        </p>
                        <Button
                            :href="installment.requires_variant_selection && matchedVariant
                                ? `/installments/initiate?type=product_variant&id=${matchedVariant.id}`
                                : installment.initiate_url"
                            :disabled="installment.requires_variant_selection && !matchedVariant"
                            variant="outline"
                            size="md"
                            class="mt-3"
                        >
                            {{ installment.requires_variant_selection && !matchedVariant ? 'Pick a variant first' : 'Pay in Installments' }}
                        </Button>
                    </div>

                    <!-- WhatsApp fallback -->
                    <a
                        :href="whatsappOrderUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#25D366] px-5 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path
                                d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.42 1.3-1.95 1.34-.5.05-.96.24-3.23-.67-2.73-1.08-4.45-3.88-4.58-4.06-.13-.18-1.1-1.47-1.1-2.8 0-1.33.7-1.98.94-2.25.24-.27.53-.34.71-.34.18 0 .35 0 .51.01.16.01.39-.06.6.46.24.58.79 2 .86 2.14.07.14.12.31.02.49-.09.18-.14.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.54.15.27.66 1.09 1.42 1.77.97.87 1.79 1.14 2.05 1.27.26.13.41.11.56-.07.15-.18.65-.76.82-1.02.17-.26.35-.22.59-.13.24.09 1.51.71 1.77.84.26.13.43.2.49.31.06.11.06.66-.18 1.34z"
                            />
                        </svg>
                        Order via WhatsApp
                    </a>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-12 max-w-3xl">
                <h2 class="text-xl font-extrabold tracking-tight">Description</h2>
                <p
                    class="mt-3 whitespace-pre-line text-base leading-relaxed text-brand-dark/70"
                >
                    {{ product.description }}
                </p>
            </div>

            <!-- Specs -->
            <div class="mt-10 max-w-3xl">
                <h2 class="text-xl font-extrabold tracking-tight">Specifications</h2>
                <dl
                    class="mt-4 divide-y divide-black/5 rounded-2xl bg-white shadow-sm ring-1 ring-black/5"
                >
                    <div
                        v-for="row in specs"
                        :key="row.label"
                        class="flex gap-4 px-4 py-3"
                    >
                        <dt
                            class="w-36 shrink-0 text-xs font-semibold uppercase tracking-wide text-brand-dark/45"
                        >
                            {{ row.label }}
                        </dt>
                        <dd class="text-sm text-brand-dark/80">{{ row.value }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Related -->
            <div v-if="related.length" class="mt-16">
                <h2 class="text-2xl font-extrabold tracking-tight">
                    More in {{ product.category }}
                </h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard
                        v-for="item in related"
                        :key="item.slug"
                        :product="item"
                        :subsidiary="subsidiary"
                    />
                </div>
            </div>
        </Section>

        <Lightbox v-model="lightboxIndex" :images="gallery" />
    </AppLayout>
</template>

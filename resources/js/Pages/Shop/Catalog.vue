<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { PackageX } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import type {
    Paginated,
    ProductCardData,
    Subsidiary,
} from '@/types/models';

const props = defineProps<{
    subsidiary: Subsidiary;
    products: Paginated<ProductCardData>;
    categories: string[];
    filters: {
        category: string | null;
        sort: string;
    };
}>();

const HERO_COPY: Record<Subsidiary, { eyebrow: string; title: string; subtitle: string }> = {
    collections: {
        eyebrow: 'BLOMFREE Collections',
        title: 'Unisex Style, Honestly Priced',
        subtitle:
            'Clothing and accessories chosen for quality and built to last — pieces for everyone.',
    },
    gadgets: {
        eyebrow: 'BLOMFREE Gadgets & Accessories',
        title: 'Tech You Can Trust',
        subtitle:
            'Phones, consoles, audio and smart devices — genuine, warranted, and ready to ship.',
    },
};

const SORT_OPTIONS = [
    { value: 'featured', label: 'Featured' },
    { value: 'newest', label: 'Newest' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
];

const hero = HERO_COPY[props.subsidiary];

function navigate(params: Record<string, string>): void {
    router.get(`/${props.subsidiary}`, params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function selectCategory(category: string | null): void {
    const params: Record<string, string> = {};
    if (category) params.category = category;
    if (props.filters.sort !== 'featured') params.sort = props.filters.sort;
    navigate(params);
}

function changeSort(event: Event): void {
    const sort = (event.target as HTMLSelectElement).value;
    const params: Record<string, string> = {};
    if (props.filters.category) params.category = props.filters.category;
    if (sort !== 'featured') params.sort = sort;
    navigate(params);
}
</script>

<template>
    <SeoMeta
        :title="(subsidiary === 'collections' ? 'BLOMFREE Collections' : 'BLOMFREE Gadgets & Accessories')"
        :description="hero.subtitle"
    />

    <AppLayout>
        <Hero :overlay="false" min-height-class="min-h-[44vh]">
            <template #overlay>
                <div
                    class="absolute inset-0"
                    style="
                        background: radial-gradient(
                            circle at 75% 30%,
                            rgba(245, 130, 32, 0.22),
                            transparent 60%
                        );
                    "
                    aria-hidden="true"
                />
            </template>
            <template #eyebrow>
                <Badge variant="info">{{ hero.eyebrow }}</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl"
                >
                    {{ hero.title }}
                </h1>
            </template>
            <template #subtitle>
                {{ hero.subtitle }}
            </template>
        </Hero>

        <Section>
            <!-- Controls -->
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <!-- Category chips -->
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition-colors"
                        :class="
                            filters.category === null
                                ? 'bg-brand-dark text-white'
                                : 'bg-white text-brand-dark/70 ring-1 ring-black/5 hover:text-brand-dark'
                        "
                        @click="selectCategory(null)"
                    >
                        All
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition-colors"
                        :class="
                            filters.category === category
                                ? 'bg-brand-dark text-white'
                                : 'bg-white text-brand-dark/70 ring-1 ring-black/5 hover:text-brand-dark'
                        "
                        @click="selectCategory(category)"
                    >
                        {{ category }}
                    </button>
                </div>

                <!-- Sort -->
                <div class="flex items-center gap-2">
                    <label
                        for="sort"
                        class="text-xs font-semibold uppercase tracking-wide text-brand-dark/55"
                    >
                        Sort
                    </label>
                    <select
                        id="sort"
                        :value="filters.sort"
                        class="rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                        @change="changeSort"
                    >
                        <option
                            v-for="option in SORT_OPTIONS"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <p class="mt-5 text-sm text-brand-dark/60">
                <span class="font-bold text-brand-dark">{{ products.total }}</span>
                {{ products.total === 1 ? 'product' : 'products' }}
            </p>

            <!-- Grid -->
            <div
                v-if="products.data.length"
                class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <ProductCard
                    v-for="product in products.data"
                    :key="product.slug"
                    :product="product"
                    :subsidiary="subsidiary"
                />
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="mt-6 flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-20 text-center"
            >
                <PackageX class="h-10 w-10 text-brand-dark/25" />
                <p class="text-lg font-bold tracking-tight">
                    Nothing here yet
                </p>
                <p class="max-w-sm text-sm text-brand-dark/55">
                    No products match this selection. Try another category or
                    clear the filter.
                </p>
                <button
                    v-if="filters.category"
                    type="button"
                    class="mt-1 text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                    @click="selectCategory(null)"
                >
                    View all products
                </button>
            </div>

            <!-- Pagination -->
            <nav
                v-if="products.last_page > 1"
                class="mt-10 flex flex-wrap items-center justify-center gap-1.5"
            >
                <template v-for="(link, i) in products.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold transition-colors"
                        :class="
                            link.active
                                ? 'bg-brand-orange text-white'
                                : 'bg-white text-brand-dark/70 ring-1 ring-black/5 hover:text-brand-dark'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="inline-flex h-9 min-w-9 items-center justify-center px-3 text-sm text-brand-dark/30"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </Section>
    </AppLayout>
</template>

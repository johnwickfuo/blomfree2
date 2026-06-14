<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    SlidersHorizontal,
    MapPin,
    Droplets,
    CreditCard,
    Search,
    X,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira } from '@/lib/format';
import type { Land, LandFilters, Paginated } from '@/types/models';

const props = defineProps<{
    lands: Paginated<Land>;
    states: string[];
    filters: LandFilters;
}>();

const form = reactive({
    state: props.filters.state ?? '',
    min_plots: props.filters.min_plots ? String(props.filters.min_plots) : '',
    price_min: props.filters.price_min ? String(props.filters.price_min) : '',
    price_max: props.filters.price_max ? String(props.filters.price_max) : '',
    installment: props.filters.installment ?? false,
    status: props.filters.status ?? 'available',
});

const showFilters = ref(false);

const plotOptions = [
    { value: '', label: 'Any number' },
    { value: '3', label: '3 or more' },
    { value: '5', label: '5 or more' },
    { value: '10', label: '10 or more' },
    { value: '20', label: '20 or more' },
];

const statusOptions = [
    { value: 'available', label: 'Available' },
    { value: 'reserved', label: 'Reserved' },
    { value: 'sold', label: 'Sold' },
    { value: 'all', label: 'All statuses' },
];

function applyFilters(): void {
    const params: Record<string, string> = {};
    if (form.state) params.state = form.state;
    if (form.min_plots) params.min_plots = form.min_plots;
    if (form.price_min) params.price_min = form.price_min;
    if (form.price_max) params.price_max = form.price_max;
    if (form.installment) params.installment = '1';
    if (form.status && form.status !== 'available') params.status = form.status;

    router.get('/lands', params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
    showFilters.value = false;
}

function resetFilters(): void {
    form.state = '';
    form.min_plots = '';
    form.price_min = '';
    form.price_max = '';
    form.installment = false;
    form.status = 'available';
    router.get('/lands', {}, { preserveScroll: true });
    showFilters.value = false;
}

function statusVariant(status: string): 'success' | 'warning' | 'neutral' {
    if (status === 'available') return 'success';
    if (status === 'reserved') return 'warning';
    return 'neutral';
}
</script>

<template>
    <SeoMeta
        title="Real Estate for Sale — BLOMFREE"
        description="Verified plots across Nigeria. Flood-free, with confirmed documents and flexible payment plans on many listings."
    />

    <AppLayout>
        <Hero :overlay="false" min-height-class="min-h-[48vh]">
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
                <Badge variant="info">BLOMFREE Real Estate</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl"
                >
                    Find Your Perfect Plot
                </h1>
            </template>
            <template #subtitle>
                Verified land at flood-free locations across Nigeria — many with
                flexible installment plans and clean, confirmed documents.
            </template>
        </Hero>

        <Section>
            <div class="grid gap-8 lg:grid-cols-4">
                <!-- Filter sidebar -->
                <aside class="lg:col-span-1">
                    <button
                        type="button"
                        class="mb-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-dark px-4 py-3 text-sm font-semibold text-white lg:hidden"
                        @click="showFilters = !showFilters"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </button>

                    <div
                        class="rounded-2xl bg-white p-5 shadow-md ring-1 ring-black/5 lg:sticky lg:top-24"
                        :class="showFilters ? 'block' : 'hidden lg:block'"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-bold tracking-tight">
                                Filter Properties
                            </h2>
                            <button
                                type="button"
                                class="text-xs font-semibold text-brand-orange hover:text-brand-orangeDark"
                                @click="resetFilters"
                            >
                                Reset
                            </button>
                        </div>

                        <form class="mt-5 space-y-4" @submit.prevent="applyFilters">
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-dark/60"
                                >
                                    State
                                </label>
                                <select
                                    v-model="form.state"
                                    class="w-full rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                                >
                                    <option value="">All states</option>
                                    <option
                                        v-for="state in states"
                                        :key="state"
                                        :value="state"
                                    >
                                        {{ state }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-dark/60"
                                >
                                    Number of plots
                                </label>
                                <select
                                    v-model="form.min_plots"
                                    class="w-full rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                                >
                                    <option
                                        v-for="opt in plotOptions"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-dark/60"
                                >
                                    Price per plot (NGN)
                                </label>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.price_min"
                                        type="number"
                                        min="0"
                                        placeholder="Min"
                                        class="w-full rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                                    />
                                    <span class="text-brand-dark/40">–</span>
                                    <input
                                        v-model="form.price_max"
                                        type="number"
                                        min="0"
                                        placeholder="Max"
                                        class="w-full rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-brand-dark/60"
                                >
                                    Status
                                </label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-xl border-brand-dark/15 text-sm focus:border-brand-orange focus:ring-brand-orange"
                                >
                                    <option
                                        v-for="opt in statusOptions"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <label
                                class="flex cursor-pointer items-center gap-2.5 rounded-xl bg-brand-cream px-3 py-2.5"
                            >
                                <input
                                    v-model="form.installment"
                                    type="checkbox"
                                    class="rounded border-brand-dark/20 text-brand-orange focus:ring-brand-orange"
                                />
                                <span class="text-sm font-medium">
                                    Installment available
                                </span>
                            </label>

                            <Button type="submit" class="w-full">
                                <Search class="h-4 w-4" />
                                Apply Filters
                            </Button>
                        </form>
                    </div>
                </aside>

                <!-- Results -->
                <div class="lg:col-span-3">
                    <p class="mb-5 text-sm text-brand-dark/60">
                        <span class="font-bold text-brand-dark">
                            {{ lands.total }}
                        </span>
                        {{ lands.total === 1 ? 'plot' : 'plots' }} found
                    </p>

                    <div
                        v-if="lands.data.length"
                        class="grid gap-6 sm:grid-cols-2"
                    >
                        <Card
                            v-for="land in lands.data"
                            :key="land.id"
                            class="flex flex-col overflow-hidden"
                        >
                            <Link
                                :href="`/lands/${land.slug}`"
                                class="block"
                            >
                                <div
                                    class="aspect-[4/3] w-full bg-brand-dark/5"
                                >
                                    <img
                                        v-if="land.cover_url"
                                        :src="land.cover_url"
                                        :alt="land.title"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                            </Link>
                            <div class="flex flex-1 flex-col gap-3 p-5">
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge variant="info">
                                        {{ land.number_of_plots }}
                                        {{
                                            land.number_of_plots === 1
                                                ? 'plot'
                                                : 'plots'
                                        }}
                                        available
                                    </Badge>
                                    <Badge
                                        v-if="land.status !== 'available'"
                                        :variant="statusVariant(land.status)"
                                    >
                                        {{ land.status }}
                                    </Badge>
                                </div>
                                <Link
                                    :href="`/lands/${land.slug}`"
                                    class="text-lg font-bold tracking-tight transition-colors hover:text-brand-orange"
                                >
                                    {{ land.title }}
                                </Link>
                                <p
                                    class="flex items-center gap-1.5 text-sm text-brand-dark/60"
                                >
                                    <MapPin class="h-4 w-4 shrink-0" />
                                    {{ land.city_or_lga }}, {{ land.state }}
                                </p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <Badge
                                        v-if="land.installment_available"
                                        variant="success"
                                    >
                                        <CreditCard class="h-3 w-3" />
                                        Installment Available
                                    </Badge>
                                    <Badge
                                        v-if="land.is_flood_free"
                                        variant="info"
                                    >
                                        <Droplets class="h-3 w-3" />
                                        Flood-Free
                                    </Badge>
                                </div>
                                <div
                                    class="mt-auto flex items-end justify-between pt-3"
                                >
                                    <div>
                                        <p
                                            class="text-xs text-brand-dark/50"
                                        >
                                            {{
                                                land.price_label ??
                                                'Price per plot'
                                            }}
                                        </p>
                                        <p
                                            class="text-lg font-extrabold tracking-tight text-brand-orange"
                                        >
                                            {{
                                                formatNaira(
                                                    land.price_per_plot,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <Link
                                        :href="`/lands/${land.slug}`"
                                        class="text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                                    >
                                        View &rarr;
                                    </Link>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-20 text-center"
                    >
                        <X class="h-10 w-10 text-brand-dark/25" />
                        <p class="text-lg font-bold tracking-tight">
                            No plots match your filters
                        </p>
                        <p class="max-w-sm text-sm text-brand-dark/55">
                            Try widening your price range or clearing a filter
                            to see more available land.
                        </p>
                        <button
                            type="button"
                            class="mt-1 text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                            @click="resetFilters"
                        >
                            Clear all filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <nav
                        v-if="lands.last_page > 1"
                        class="mt-10 flex flex-wrap items-center justify-center gap-1.5"
                    >
                        <template
                            v-for="(link, i) in lands.links"
                            :key="i"
                        >
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
                </div>
            </div>
        </Section>
    </AppLayout>
</template>

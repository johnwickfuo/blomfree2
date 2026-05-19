<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    CalendarCheck,
    ShoppingCart,
    Check,
    Syringe,
    Package,
} from 'lucide-vue-next';
import { openCartDrawer } from '@/lib/cart';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import Lightbox from '@/Components/Lightbox.vue';
import AnimalCard from '@/Components/AnimalCard.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import JsonLd from '@/Components/JsonLd.vue';
import { formatNaira } from '@/lib/format';
import type { Animal } from '@/types/models';

const props = defineProps<{
    animal: Animal;
    related: Animal[];
}>();

const VACCINATION_LABELS: Record<string, string> = {
    first_shots: 'First shots',
    second_shots: 'Second shots',
    rabies: 'Rabies',
    dewormed: 'Dewormed',
};

const CATEGORY_LABELS: Record<string, string> = {
    dog: 'Dog',
    cat: 'Cat',
    rabbit: 'Rabbit',
    grasscutter: 'Grasscutter',
    other: 'Other',
};

const gallery = computed<string[]>(() => props.animal.gallery_urls ?? []);
const lightboxIndex = ref<number | null>(null);
const addingToCart = ref(false);

const restockNotifyUrl = computed(() => {
    const text = `Hi BLOMFREE, please notify me when ${props.animal.name} (${props.animal.breed}) is back in stock.`;
    return `https://wa.me/2348103965317?text=${encodeURIComponent(text)}`;
});

function buyOnline(): void {
    if (addingToCart.value) return;

    addingToCart.value = true;
    router.post(
        '/cart/add',
        {
            cartable_type: 'animal',
            cartable_id: props.animal.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => openCartDrawer(),
            onFinish: () => {
                addingToCart.value = false;
            },
        },
    );
}

const isPool = computed(() => props.animal.listing_type === 'pool');
const isSold = computed(() => props.animal.effective_availability === 'sold');

const showInspect = computed(
    () => props.animal.supports_inspection && !isSold.value,
);
const showBuy = computed(
    () => props.animal.supports_online_purchase && !isSold.value,
);

const availabilityBadge = computed<{
    label: string;
    variant: 'success' | 'warning' | 'neutral';
}>(() => {
    switch (props.animal.effective_availability) {
        case 'available':
            return { label: 'Available', variant: 'success' };
        case 'reserved':
            return { label: 'Reserved', variant: 'warning' };
        case 'on_order':
            return { label: 'On Order', variant: 'warning' };
        default:
            return { label: 'Sold', variant: 'neutral' };
    }
});

const sexLabel = computed<string | null>(() => {
    if (props.animal.sex === 'male') return 'Male';
    if (props.animal.sex === 'female') return 'Female';
    if (props.animal.sex === 'mixed') return 'Mixed';
    return null;
});

const vaccinations = computed(() => {
    if (!props.animal.vaccination_status || !props.animal.vaccination_status.length) {
        return [];
    }

    return Object.entries(VACCINATION_LABELS).map(([key, label]) => ({
        key,
        label,
        present: props.animal.vaccination_status?.includes(key) ?? false,
    }));
});

const infoRows = computed(() => {
    const rows: { label: string; value: string }[] = [];
    const a = props.animal;

    rows.push({ label: 'Breed', value: a.breed });
    if (a.origin) rows.push({ label: 'Origin', value: a.origin });
    if (sexLabel.value) rows.push({ label: 'Sex', value: sexLabel.value });
    if (a.age_text) rows.push({ label: 'Age', value: a.age_text });
    if (a.typical_adult_size) {
        rows.push({ label: 'Typical adult size', value: a.typical_adult_size });
    }
    if (a.temperament) rows.push({ label: 'Temperament', value: a.temperament });
    if (a.parents_info) rows.push({ label: 'Parents', value: a.parents_info });

    return rows;
});

const animalSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: props.animal.name,
    description: props.animal.description,
    category: props.animal.category,
    brand: { '@type': 'Organization', name: 'BLOMFREE Kennel & Farm' },
    image: props.animal.gallery_urls?.[0] ?? props.animal.cover_url ?? undefined,
    offers: {
        '@type': 'Offer',
        price: props.animal.price,
        priceCurrency: 'NGN',
        availability:
            props.animal.effective_availability === 'available'
                ? 'https://schema.org/InStock'
                : props.animal.effective_availability === 'reserved' ||
                    props.animal.effective_availability === 'on_order'
                  ? 'https://schema.org/LimitedAvailability'
                  : 'https://schema.org/SoldOut',
    },
}));
</script>

<template>
    <SeoMeta
        :title="animal.name + ' — ' + animal.breed + ' | BLOMFREE Kennel & Farm'"
        :description="animal.breed + ' — ' + (animal.origin ?? 'BLOMFREE Kennel & Farm')"
        :image="animal.cover_url ?? undefined"
    />
    <JsonLd :schema="animalSchema" />

    <AppLayout>
        <Section>
            <Link
                href="/kennel-farm"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to Kennel &amp; Farm
            </Link>

            <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:gap-12">
                <!-- Gallery -->
                <div>
                    <div class="overflow-hidden rounded-3xl">
                        <button
                            v-if="gallery.length"
                            type="button"
                            class="block aspect-[4/3] w-full bg-brand-dark/5"
                            @click="lightboxIndex = 0"
                        >
                            <img
                                :src="gallery[0]"
                                :alt="animal.name"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                            />
                        </button>
                        <div
                            v-else
                            class="flex aspect-[4/3] w-full items-center justify-center bg-brand-dark/5 text-sm text-brand-dark/40"
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
                            <img
                                :src="image"
                                alt=""
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="info">
                            {{ CATEGORY_LABELS[animal.category] }}
                        </Badge>
                        <Badge :variant="availabilityBadge.variant">
                            {{ availabilityBadge.label }}
                        </Badge>
                        <Badge v-if="sexLabel" variant="neutral">
                            {{ sexLabel }}
                        </Badge>
                        <Badge
                            v-if="!isPool && animal.age_text"
                            variant="neutral"
                        >
                            {{ animal.age_text }}
                        </Badge>
                        <Badge v-if="isPool" variant="success">
                            <Package class="h-3 w-3" />
                            {{ animal.stock }} available
                        </Badge>
                    </div>

                    <h1
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        {{ animal.name }}
                    </h1>
                    <p class="mt-1 text-base text-brand-dark/60">
                        {{ animal.breed }}
                    </p>

                    <div class="mt-5">
                        <p class="text-xs text-brand-dark/50">
                            {{ isPool ? 'Price per animal' : 'Price' }}
                        </p>
                        <p
                            class="text-3xl font-extrabold tracking-tight text-brand-orange"
                        >
                            {{ formatNaira(animal.price) }}
                        </p>
                    </div>

                    <!-- CTAs -->
                    <div v-if="!isSold" class="mt-6">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <Button
                                v-if="showInspect"
                                :href="`/kennel-farm/${animal.slug}/inspect`"
                                size="lg"
                                class="w-full"
                            >
                                <CalendarCheck class="h-4 w-4" />
                                Book Inspection
                            </Button>
                            <Button
                                v-if="showBuy"
                                size="lg"
                                variant="secondary"
                                class="w-full"
                                :disabled="addingToCart"
                                @click="buyOnline"
                            >
                                <ShoppingCart class="h-4 w-4" />
                                {{ addingToCart ? 'Adding…' : 'Buy Online' }}
                            </Button>
                        </div>
                    </div>
                    <div
                        v-else-if="isPool"
                        class="mt-6 rounded-2xl bg-brand-dark/5 p-4 text-sm"
                    >
                        <p class="font-semibold text-brand-dark">
                            Out of stock
                        </p>
                        <p class="mt-1 text-brand-dark/65">
                            Contact us and we'll let you know when this
                            breed is back in stock.
                        </p>
                        <a
                            :href="restockNotifyUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-3 inline-flex items-center rounded-full bg-[#25D366] px-4 py-2 text-xs font-semibold text-white"
                        >
                            Notify me on WhatsApp
                        </a>
                    </div>
                    <div
                        v-else
                        class="mt-6 rounded-2xl bg-brand-dark/5 px-4 py-3 text-sm font-medium text-brand-dark/60"
                    >
                        This animal has been sold.
                    </div>

                    <!-- Info rows -->
                    <dl
                        class="mt-7 divide-y divide-black/5 rounded-2xl bg-white shadow-sm ring-1 ring-black/5"
                    >
                        <div
                            v-for="row in infoRows"
                            :key="row.label"
                            class="flex gap-4 px-4 py-3"
                        >
                            <dt
                                class="w-32 shrink-0 text-xs font-semibold uppercase tracking-wide text-brand-dark/45"
                            >
                                {{ row.label }}
                            </dt>
                            <dd class="text-sm text-brand-dark/80">
                                {{ row.value }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-12 max-w-3xl">
                <h2 class="text-xl font-extrabold tracking-tight">
                    About {{ animal.name }}
                </h2>
                <p
                    class="mt-3 whitespace-pre-line text-base leading-relaxed text-brand-dark/70"
                >
                    {{ animal.description }}
                </p>
            </div>

            <!-- Highlights -->
            <div v-if="animal.highlights.length" class="mt-10 max-w-3xl">
                <h2 class="text-xl font-extrabold tracking-tight">
                    Highlights
                </h2>
                <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                    <li
                        v-for="highlight in animal.highlights"
                        :key="highlight"
                        class="flex items-center gap-2.5 text-sm text-brand-dark/75"
                    >
                        <span
                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700"
                        >
                            <Check class="h-3.5 w-3.5" />
                        </span>
                        {{ highlight }}
                    </li>
                </ul>
            </div>

            <!-- Vaccination status -->
            <div v-if="vaccinations.length" class="mt-10 max-w-3xl">
                <h2
                    class="flex items-center gap-2 text-xl font-extrabold tracking-tight"
                >
                    <Syringe class="h-5 w-5 text-brand-orange" />
                    Vaccination status
                </h2>
                <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                    <li
                        v-for="vaccine in vaccinations"
                        :key="vaccine.key"
                        class="flex items-center gap-2.5 text-sm"
                        :class="
                            vaccine.present
                                ? 'text-brand-dark/75'
                                : 'text-brand-dark/35'
                        "
                    >
                        <span
                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
                            :class="
                                vaccine.present
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-brand-dark/5 text-brand-dark/30'
                            "
                        >
                            <Check class="h-3.5 w-3.5" />
                        </span>
                        {{ vaccine.label }}
                    </li>
                </ul>
            </div>

            <!-- Related -->
            <div v-if="related.length" class="mt-16">
                <h2 class="text-2xl font-extrabold tracking-tight">
                    More {{ CATEGORY_LABELS[animal.category].toLowerCase() }}
                    listings
                </h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <AnimalCard
                        v-for="item in related"
                        :key="item.id"
                        :animal="item"
                    />
                </div>
            </div>
        </Section>

        <Lightbox v-model="lightboxIndex" :images="gallery" />
    </AppLayout>
</template>

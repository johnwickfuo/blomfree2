<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    MapPin,
    LayoutGrid,
    Ruler,
    Banknote,
    FileCheck,
    Check,
    Route,
    Droplets,
    CreditCard,
    Landmark,
    Phone,
    CalendarCheck,
    ChevronLeft,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';
import Lightbox from '@/Components/Lightbox.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import JsonLd from '@/Components/JsonLd.vue';
import { formatNaira } from '@/lib/format';
import type { Land } from '@/types/models';

interface InstallmentSummary {
    enabled: boolean;
    down_payment_percentage: number;
    maximum_length_months: number;
    initiate_url: string;
}

const props = defineProps<{
    land: Land;
    related: Land[];
    installment?: InstallmentSummary | null;
}>();

const WHATSAPP_URL = 'https://wa.me/2348103965317';
const TEL_URL = 'tel:+2348103965317';

const DOCUMENT_LABELS: Record<string, string> = {
    c_of_o: 'Certificate of Occupancy',
    governors_consent: "Governor's Consent",
    deed_of_assignment: 'Deed of Assignment',
    survey_plan: 'Survey Plan',
    receipt_of_purchase: 'Receipt of Purchase',
};

const gallery = computed<string[]>(() => props.land.gallery_urls ?? []);
const lightboxIndex = ref<number | null>(null);

const documents = computed(() =>
    Object.entries(DOCUMENT_LABELS).map(([key, label]) => ({
        key,
        label,
        present: props.land.document_status.includes(key),
    })),
);

const statusVariant = computed<'success' | 'warning' | 'neutral'>(() => {
    if (props.land.status === 'available') return 'success';
    if (props.land.status === 'reserved') return 'warning';
    return 'neutral';
});

const showTotal = computed(() => props.land.number_of_plots > 1);

const landSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'RealEstateListing',
    name: props.land.title,
    description: props.land.description,
    url: typeof window !== 'undefined'
        ? window.location.origin + '/lands/' + props.land.slug
        : '',
    image: props.land.gallery_urls?.[0] ?? props.land.cover_url ?? undefined,
    address: {
        '@type': 'PostalAddress',
        streetAddress: props.land.location_address,
        addressLocality: props.land.city_or_lga,
        addressRegion: props.land.state,
        addressCountry: 'NG',
    },
    offers: {
        '@type': 'Offer',
        price: props.land.price_per_plot,
        priceCurrency: 'NGN',
        availability:
            props.land.status === 'available'
                ? 'https://schema.org/InStock'
                : props.land.status === 'reserved'
                  ? 'https://schema.org/LimitedAvailability'
                  : 'https://schema.org/SoldOut',
    },
}));
</script>

<template>
    <SeoMeta
        :title="land.title + ' — BLOMFREE Estates'"
        :description="land.number_of_plots + ' plot(s) in ' + land.city_or_lga + ', ' + land.state + '. ' + (land.is_flood_free ? 'Flood-free. ' : '') + 'From ' + land.price_per_plot + ' NGN per plot.'"
        :image="land.cover_url ?? undefined"
    />
    <JsonLd :schema="landSchema" />

    <AppLayout>
        <Section>
            <Link
                href="/lands"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-dark/60 transition-colors hover:text-brand-orange"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to all lands
            </Link>

            <div class="mt-6 grid gap-10 lg:grid-cols-3 lg:gap-12">
                <!-- Main content -->
                <div class="lg:col-span-2">
                    <!-- Gallery -->
                    <div class="overflow-hidden rounded-3xl">
                        <button
                            v-if="gallery.length"
                            type="button"
                            class="block aspect-[16/10] w-full bg-brand-dark/5"
                            @click="lightboxIndex = 0"
                        >
                            <img
                                :src="gallery[0]"
                                :alt="land.title"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                            />
                        </button>
                        <div
                            v-else
                            class="flex aspect-[16/10] w-full items-center justify-center bg-brand-dark/5 text-sm text-brand-dark/40"
                        >
                            No images available
                        </div>
                    </div>
                    <div
                        v-if="gallery.length > 1"
                        class="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-5"
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

                    <!-- Title block -->
                    <div class="mt-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge :variant="statusVariant">
                                {{ land.status }}
                            </Badge>
                            <Badge variant="info">{{ land.state }}</Badge>
                            <Badge
                                v-if="land.installment_available"
                                variant="success"
                            >
                                Installment Available
                            </Badge>
                        </div>
                        <h1
                            class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                        >
                            {{ land.title }}
                        </h1>
                        <p
                            class="mt-2 flex items-center gap-1.5 text-base text-brand-dark/65"
                        >
                            <MapPin class="h-4 w-4 shrink-0" />
                            {{ land.location_address }}
                        </p>
                    </div>

                    <!-- Key facts -->
                    <div
                        class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4"
                    >
                        <div
                            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                        >
                            <LayoutGrid class="h-5 w-5 text-brand-orange" />
                            <p
                                class="mt-2 text-lg font-extrabold tracking-tight"
                            >
                                {{ land.number_of_plots }}
                            </p>
                            <p class="text-xs text-brand-dark/55">
                                Plots available
                            </p>
                        </div>
                        <div
                            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                        >
                            <Ruler class="h-5 w-5 text-brand-orange" />
                            <p
                                class="mt-2 text-lg font-extrabold tracking-tight"
                            >
                                {{ land.plot_size_sqm }} sqm
                            </p>
                            <p class="text-xs text-brand-dark/55">
                                Per plot size
                            </p>
                        </div>
                        <div
                            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                        >
                            <Banknote class="h-5 w-5 text-brand-orange" />
                            <p
                                class="mt-2 text-lg font-extrabold tracking-tight"
                            >
                                {{ formatNaira(land.price_per_plot) }}
                            </p>
                            <p class="text-xs text-brand-dark/55">
                                Price per plot
                            </p>
                        </div>
                        <div
                            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                        >
                            <FileCheck class="h-5 w-5 text-brand-orange" />
                            <p
                                class="mt-2 text-lg font-extrabold tracking-tight"
                            >
                                {{ land.document_status.length }} / 5
                            </p>
                            <p class="text-xs text-brand-dark/55">
                                Documents in place
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-10">
                        <h2 class="text-xl font-extrabold tracking-tight">
                            About this land
                        </h2>
                        <p
                            class="mt-3 whitespace-pre-line text-base leading-relaxed text-brand-dark/70"
                        >
                            {{ land.description }}
                        </p>
                    </div>

                    <!-- Features -->
                    <div v-if="land.features.length" class="mt-10">
                        <h2 class="text-xl font-extrabold tracking-tight">
                            Features
                        </h2>
                        <ul
                            class="mt-4 grid gap-2.5 sm:grid-cols-2"
                        >
                            <li
                                v-for="feature in land.features"
                                :key="feature"
                                class="flex items-center gap-2.5 text-sm text-brand-dark/75"
                            >
                                <span
                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700"
                                >
                                    <Check class="h-3.5 w-3.5" />
                                </span>
                                {{ feature }}
                            </li>
                        </ul>
                    </div>

                    <!-- Document status -->
                    <div class="mt-10">
                        <h2 class="text-xl font-extrabold tracking-tight">
                            Document status
                        </h2>
                        <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                            <li
                                v-for="doc in documents"
                                :key="doc.key"
                                class="flex items-center gap-2.5 text-sm"
                                :class="
                                    doc.present
                                        ? 'text-brand-dark/75'
                                        : 'text-brand-dark/35'
                                "
                            >
                                <span
                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
                                    :class="
                                        doc.present
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-brand-dark/5 text-brand-dark/30'
                                    "
                                >
                                    <FileCheck class="h-3.5 w-3.5" />
                                </span>
                                {{ doc.label }}
                            </li>
                        </ul>
                    </div>

                    <!-- Why this land -->
                    <div class="mt-10">
                        <h2 class="text-xl font-extrabold tracking-tight">
                            Why this land?
                        </h2>
                        <div class="mt-4 space-y-3">
                            <div
                                v-if="land.close_to_landmarks && land.close_to_landmarks.length"
                                class="flex gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                            >
                                <Landmark
                                    class="h-5 w-5 shrink-0 text-brand-orange"
                                />
                                <div>
                                    <p class="text-sm font-bold">
                                        Close to key landmarks
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm text-brand-dark/65"
                                    >
                                        {{
                                            land.close_to_landmarks.join(' • ')
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="land.has_good_access_road"
                                class="flex gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                            >
                                <Route
                                    class="h-5 w-5 shrink-0 text-brand-orange"
                                />
                                <div>
                                    <p class="text-sm font-bold">
                                        Good access road
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm text-brand-dark/65"
                                    >
                                        The estate is reachable on a motorable
                                        road all year round.
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="land.is_flood_free"
                                class="flex gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                            >
                                <Droplets
                                    class="h-5 w-5 shrink-0 text-brand-orange"
                                />
                                <div>
                                    <p class="text-sm font-bold">
                                        Flood-free location
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm text-brand-dark/65"
                                    >
                                        Dry, firm ground that stays usable
                                        through the rainy season.
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="land.installment_available"
                                class="flex gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"
                            >
                                <CreditCard
                                    class="h-5 w-5 shrink-0 text-brand-orange"
                                />
                                <div>
                                    <p class="text-sm font-bold">
                                        Flexible installment plan
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm text-brand-dark/65"
                                    >
                                        Spread payment over time — talk to us
                                        about a plan that works for you.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky contact rail (desktop) -->
                <div class="lg:col-span-1">
                    <Card
                        :hover="false"
                        class="hidden p-6 lg:sticky lg:top-24 lg:block"
                    >
                        <p class="text-xs text-brand-dark/50">
                            {{ land.price_label ?? 'Price per plot' }}
                        </p>
                        <p
                            class="text-3xl font-extrabold tracking-tight text-brand-orange"
                        >
                            {{ formatNaira(land.price_per_plot) }}
                        </p>
                        <p
                            v-if="showTotal"
                            class="mt-1 text-sm text-brand-dark/60"
                        >
                            Total for all {{ land.number_of_plots }} plots:
                            <span class="font-bold text-brand-dark">
                                {{ formatNaira(land.total_price) }}
                            </span>
                        </p>

                        <div v-if="installment" class="mt-4 rounded-xl bg-brand-orange/10 p-3 text-xs text-brand-dark/80">
                            <strong class="text-brand-orange">Pay in installments:</strong>
                            {{ installment.down_payment_percentage }}% down, complete within {{ installment.maximum_length_months }} months. 10% forfeiture if not completed.
                        </div>

                        <div class="mt-5 flex flex-col gap-2.5">
                            <Button
                                v-if="installment"
                                :href="installment.initiate_url"
                                variant="primary"
                                class="w-full"
                            >
                                Pay in Installments
                            </Button>
                            <Button
                                :href="`/lands/${land.slug}/inspect`"
                                :variant="installment ? 'secondary' : 'primary'"
                                class="w-full"
                            >
                                <CalendarCheck class="h-4 w-4" />
                                Book Inspection
                            </Button>
                            <Button
                                :href="TEL_URL"
                                external
                                variant="secondary"
                                class="w-full"
                            >
                                <Phone class="h-4 w-4" />
                                Call Now
                            </Button>
                            <Button
                                :href="WHATSAPP_URL"
                                external
                                variant="outline"
                                class="w-full"
                            >
                                WhatsApp
                            </Button>
                        </div>
                        <p
                            class="mt-4 text-center text-xs text-brand-dark/45"
                        >
                            Free site inspection — confirmed within 24 hours.
                        </p>
                    </Card>
                </div>
            </div>

            <!-- Related lands -->
            <div v-if="related.length" class="mt-16">
                <h2 class="text-2xl font-extrabold tracking-tight">
                    More land in {{ land.state }}
                </h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="item in related"
                        :key="item.id"
                        class="flex flex-col overflow-hidden"
                    >
                        <Link
                            :href="`/lands/${item.slug}`"
                            class="block aspect-[4/3] w-full bg-brand-dark/5"
                        >
                            <img
                                v-if="item.cover_url"
                                :src="item.cover_url"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                        </Link>
                        <div class="flex flex-1 flex-col gap-2 p-5">
                            <Link
                                :href="`/lands/${item.slug}`"
                                class="font-bold tracking-tight transition-colors hover:text-brand-orange"
                            >
                                {{ item.title }}
                            </Link>
                            <p
                                class="flex items-center gap-1.5 text-sm text-brand-dark/60"
                            >
                                <MapPin class="h-4 w-4 shrink-0" />
                                {{ item.city_or_lga }}, {{ item.state }}
                            </p>
                            <p
                                class="mt-auto pt-2 text-base font-extrabold tracking-tight text-brand-orange"
                            >
                                {{ formatNaira(item.price_per_plot) }}
                            </p>
                        </div>
                    </Card>
                </div>
            </div>
        </Section>

        <!-- Sticky contact bar (mobile) -->
        <div
            class="sticky bottom-0 z-20 border-t border-black/10 bg-white/95 px-4 py-3 backdrop-blur lg:hidden"
        >
            <div class="flex items-center gap-2">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs text-brand-dark/50">
                        {{ land.price_label ?? 'Price per plot' }}
                    </p>
                    <p
                        class="text-lg font-extrabold leading-tight tracking-tight text-brand-orange"
                    >
                        {{ formatNaira(land.price_per_plot) }}
                    </p>
                </div>
                <a
                    :href="TEL_URL"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-dark text-white"
                    aria-label="Call now"
                >
                    <Phone class="h-5 w-5" />
                </a>
                <a
                    :href="WHATSAPP_URL"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#25D366] text-white"
                    aria-label="Chat on WhatsApp"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="h-5 w-5"
                        aria-hidden="true"
                    >
                        <path
                            d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.42 1.3-1.95 1.34-.5.05-.96.24-3.23-.67-2.73-1.08-4.45-3.88-4.58-4.06-.13-.18-1.1-1.47-1.1-2.8 0-1.33.7-1.98.94-2.25.24-.27.53-.34.71-.34.18 0 .35 0 .51.01.16.01.39-.06.6.46.24.58.79 2 .86 2.14.07.14.12.31.02.49-.09.18-.14.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.54.15.27.66 1.09 1.42 1.77.97.87 1.79 1.14 2.05 1.27.26.13.41.11.56-.07.15-.18.65-.76.82-1.02.17-.26.35-.22.59-.13.24.09 1.51.71 1.77.84.26.13.43.2.49.31.06.11.06.66-.18 1.34z"
                        />
                    </svg>
                </a>
                <Button
                    :href="`/lands/${land.slug}/inspect`"
                    size="sm"
                    class="shrink-0"
                >
                    <CalendarCheck class="h-4 w-4" />
                    Inspect
                </Button>
            </div>
        </div>

        <Lightbox v-model="lightboxIndex" :images="gallery" />
    </AppLayout>
</template>

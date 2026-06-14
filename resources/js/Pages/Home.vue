<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PullQuote from '@/Components/PullQuote.vue';
import CtaBand from '@/Components/CtaBand.vue';
import SubsidiaryCard from '@/Components/SubsidiaryCard.vue';
import { subsidiaries } from '@/data/subsidiaries';

interface FeaturedItem {
    id: number;
    title: string;
    image: string | null;
    meta: string | null;
    href: string;
}

type TabKey = 'lands' | 'animals' | 'collections' | 'gadgets';

type FeaturedData = Record<TabKey, FeaturedItem[]>;

const props = withDefaults(
    defineProps<{
        featured?: FeaturedData;
    }>(),
    {
        featured: () => ({
            lands: [],
            animals: [],
            collections: [],
            gadgets: [],
        }),
    },
);

const tabs: { key: TabKey; label: string }[] = [
    { key: 'lands', label: 'Real Estate' },
    { key: 'animals', label: 'Animals' },
    { key: 'collections', label: 'Collections' },
    { key: 'gadgets', label: 'Gadgets' },
];

const activeTab = ref<TabKey>('lands');
const activeItems = computed<FeaturedItem[]>(() => props.featured[activeTab.value]);
const activeTabLabel = computed(
    () => tabs.find((t) => t.key === activeTab.value)?.label ?? '',
);

// Placeholder figures — easy to edit once real numbers are available.
const stats: { value: string; label: string }[] = [
    { value: '40+', label: 'Properties Sold' },
    { value: 'Premium', label: 'Breeds Imported' },
    { value: '1000+', label: 'Customers' },
    { value: '100%', label: 'Reliable' },
];

const scrollToSubsidiaries = (): void => {
    document
        .getElementById('subsidiaries')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const page = usePage();
const ceoImage = computed<string>(() => {
    const url = (page.props.branding as { ceo_image_url?: string | null } | undefined)
        ?.ceo_image_url;
    return url || '/images/ceo.jpg';
});
</script>

<template>
    <SeoMeta
        title="BLOMFREE & CO. — Quality is Priceless"
        description="Land, premium livestock, fashion and gadgets — one trusted Nigerian group across four industries."
    />

    <AppLayout>
        <!-- 1. Hero -->
        <Hero :overlay="false" min-height-class="min-h-[90vh]">
            <template #overlay>
                <!-- Subtle orange radial gradient -->
                <div
                    class="absolute inset-0"
                    style="
                        background: radial-gradient(
                            circle at 72% 32%,
                            rgba(245, 130, 32, 0.22),
                            transparent 58%
                        );
                    "
                    aria-hidden="true"
                />
                <!-- CEO portrait (desktop only) -->
                <div
                    class="absolute inset-y-0 right-0 hidden w-[46%] lg:block xl:w-[42%]"
                >
                    <img
                        :src="ceoImage"
                        alt="Saturday Emomotimi Charles, CEO of BLOMFREE & CO."
                        class="h-full w-full object-cover object-top"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/60 to-transparent"
                        aria-hidden="true"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-brand-dark/70 via-transparent to-transparent"
                        aria-hidden="true"
                    />
                </div>
            </template>

            <template #eyebrow>
                <Badge variant="info">BLOMFREE &amp; CO. NIG. LTD</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl"
                >
                    One Group. Four Industries.
                    <span class="text-brand-orange">
                        Endless Possibilities.
                    </span>
                </h1>
            </template>
            <template #subtitle>
                BLOMFREE &amp; CO. NIG. LTD is your trusted Nigerian partner for
                land, premium livestock, fashion, and gadgets.
            </template>
            <template #actions>
                <Button size="lg" @click="scrollToSubsidiaries">
                    Explore Subsidiaries
                </Button>
                <Button href="/contact" variant="outline" size="lg">
                    Contact Us
                </Button>
            </template>
        </Hero>

        <!-- 2. Orange callout band -->
        <Link
            href="/about"
            class="group block bg-brand-orange transition-colors duration-300 hover:bg-brand-orangeDark"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-7 sm:px-6 sm:py-8 lg:px-8"
            >
                <p
                    class="text-xl font-extrabold tracking-tight text-white sm:text-2xl"
                >
                    Why Haven&rsquo;t You Patronized Us?
                </p>
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-white"
                >
                    <span class="hidden sm:inline">Find out why</span>
                    <ArrowRight
                        class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1"
                    />
                </span>
            </div>
        </Link>

        <!-- 3. CEO intro -->
        <Section>
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div v-reveal class="relative">
                    <div
                        class="absolute -bottom-5 -right-5 h-2/3 w-2/3 rounded-3xl bg-brand-orange/15"
                        aria-hidden="true"
                    />
                    <img
                        :src="ceoImage"
                        alt="Saturday Emomotimi Charles, CEO"
                        class="relative aspect-[4/5] w-full rounded-3xl object-cover object-top shadow-xl"
                    />
                </div>

                <div v-reveal="120" class="flex flex-col gap-6">
                    <div>
                        <Badge variant="info">Leadership</Badge>
                        <h2
                            class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                        >
                            Meet Saturday Emomotimi Charles, CEO
                        </h2>
                    </div>
                    <div
                        class="space-y-4 text-base leading-relaxed text-brand-dark/70"
                    >
                        <p>
                            Under the leadership of Saturday Emomotimi Charles,
                            BLOMFREE &amp; CO. NIG. LTD has grown from a single
                            vision into a multi-industry group serving customers
                            right across Nigeria.
                        </p>
                        <p>
                            That vision is simple: make quality accessible.
                            Whether it is a flood-free plot of land, a healthy
                            well-bred animal, a wardrobe upgrade, or the latest
                            device, every BLOMFREE division is held to the same
                            uncompromising standard.
                        </p>
                        <p>
                            Trust is earned through consistency. We say what we
                            will do, and we do it &mdash; and that is why our
                            customers keep coming back, and keep bringing others
                            with them.
                        </p>
                    </div>
                    <PullQuote cite="Saturday Emomotimi Charles, CEO">
                        Quality is Priceless
                    </PullQuote>
                </div>
            </div>
        </Section>

        <!-- 4. Subsidiaries grid -->
        <Section id="subsidiaries">
            <div v-reveal class="max-w-2xl">
                <Badge variant="info">Our Divisions</Badge>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    Four divisions, one standard of quality
                </h2>
                <p class="mt-4 text-base text-brand-dark/70">
                    Explore the four arms of BLOMFREE &amp; CO. &mdash; each
                    built to deliver value you can trust.
                </p>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                <SubsidiaryCard
                    v-for="(item, i) in subsidiaries"
                    :key="item.href"
                    v-reveal="i * 80"
                    :name="item.name"
                    :tagline="item.tagline"
                    :href="item.href"
                    :icon="item.icon"
                />
            </div>
        </Section>

        <!-- 5. Featured listings -->
        <Section>
            <div
                v-reveal
                class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="max-w-xl">
                    <Badge variant="info">Handpicked</Badge>
                    <h2
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        Featured listings
                    </h2>
                    <p class="mt-4 text-base text-brand-dark/70">
                        A preview of standout offerings from across our
                        subsidiaries.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition-colors"
                        :class="
                            activeTab === tab.key
                                ? 'bg-brand-dark text-white'
                                : 'bg-white text-brand-dark/70 ring-1 ring-black/5 hover:text-brand-dark'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div v-reveal="100" class="mt-10">
                <div
                    v-if="activeItems.length"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="item in activeItems"
                        :key="item.id"
                        class="overflow-hidden"
                    >
                        <div class="aspect-[4/3] w-full bg-brand-cream">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="flex flex-col gap-2 p-5">
                            <h3 class="font-bold tracking-tight">
                                {{ item.title }}
                            </h3>
                            <p
                                v-if="item.meta"
                                class="text-sm text-brand-dark/60"
                            >
                                {{ item.meta }}
                            </p>
                            <Link
                                :href="item.href"
                                class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange transition-colors hover:text-brand-orangeDark"
                            >
                                View details
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </Card>
                </div>

                <!-- Coming Soon empty state -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-16 text-center"
                >
                    <Sparkles class="h-9 w-9 text-brand-orange/60" />
                    <p class="text-lg font-bold tracking-tight">Coming Soon</p>
                    <p class="max-w-sm text-sm text-brand-dark/55">
                        Featured {{ activeTabLabel }} will appear here as our
                        {{ activeTabLabel.toLowerCase() }} catalogue goes live.
                    </p>
                </div>
            </div>
        </Section>

        <!-- 6. Trust band -->
        <Section>
            <div
                class="rounded-3xl bg-white px-6 py-10 shadow-md ring-1 ring-black/5 sm:px-10 sm:py-12"
            >
                <div class="grid grid-cols-2 gap-8 lg:grid-cols-4">
                    <div
                        v-for="(stat, i) in stats"
                        :key="stat.label"
                        v-reveal="i * 80"
                        class="text-center"
                    >
                        <p
                            class="text-3xl font-extrabold tracking-tight text-brand-orange sm:text-4xl"
                        >
                            {{ stat.value }}
                        </p>
                        <p
                            class="mt-1.5 text-sm font-semibold text-brand-dark/60"
                        >
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </Section>

        <!-- 7. Final CTA band -->
        <CtaBand
            title="Ready to experience quality?"
            subtitle="Reach out today — our team is ready to help you find land, livestock, fashion, or the latest gadgets."
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Target, Eye, Gem } from 'lucide-vue-next';
import SeoMeta from '@/Components/SeoMeta.vue';
import { computed, type Component } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PullQuote from '@/Components/PullQuote.vue';
import CtaBand from '@/Components/CtaBand.vue';
import { subsidiaries } from '@/data/subsidiaries';

interface Principle {
    title: string;
    body: string;
    icon: Component;
}

const page = usePage();
const ceoImage = computed<string>(() => {
    const url = (page.props.branding as { ceo_image_url?: string | null } | undefined)
        ?.ceo_image_url;
    return url || '/images/ceo.jpg';
});

const principles: Principle[] = [
    {
        title: 'Our Mission',
        body: 'To make genuine quality accessible to every Nigerian — across land, livestock, fashion, and technology — through honest dealing and dependable service.',
        icon: Target,
    },
    {
        title: 'Our Vision',
        body: 'To be the most trusted multi-industry group in Nigeria, known for raising the standard in every market we enter.',
        icon: Eye,
    },
    {
        title: 'Our Values',
        body: 'Integrity in every transaction, quality without compromise, and lasting relationships built on customers who return and refer.',
        icon: Gem,
    },
];
</script>

<template>
    <SeoMeta
        title="About BLOMFREE & CO. — Built in Nigeria"
        description="One Nigerian group, four industries — land, livestock, fashion and tech — with a single standard of quality."
    />

    <AppLayout>
        <!-- 1. Hero -->
        <Hero :overlay="false" align="center" min-height-class="min-h-[55vh]">
            <template #overlay>
                <div
                    class="absolute inset-0"
                    style="
                        background: radial-gradient(
                            circle at 50% 0%,
                            rgba(245, 130, 32, 0.2),
                            transparent 60%
                        );
                    "
                    aria-hidden="true"
                />
            </template>
            <template #eyebrow>
                <Badge variant="info">Est. in Nigeria</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl"
                >
                    About BLOMFREE &amp; CO.
                </h1>
            </template>
            <template #subtitle>
                One Nigerian group, four industries, and a single promise —
                quality you can build a life on.
            </template>
        </Hero>

        <!-- 2. The Story -->
        <Section width="narrow">
            <div v-reveal>
                <Badge variant="info">Our Story</Badge>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    Built in Nigeria, for Nigeria
                </h2>
            </div>
            <div
                v-reveal="100"
                class="mt-6 space-y-5 text-base leading-relaxed text-brand-dark/70"
            >
                <p>
                    BLOMFREE &amp; CO. NIG. LTD began with a straightforward
                    observation: too many Nigerians were being sold less than
                    they paid for. Land that flooded, animals that arrived
                    unwell, products that did not last. There was room for a
                    company that simply did things properly.
                </p>
                <p>
                    From that starting point, the group grew &mdash;
                    deliberately, one division at a time. Estates and
                    properties came first, then a kennel and farm, then
                    collections, and then gadgets and accessories. Different
                    markets, but the same operating principle behind each one.
                </p>
                <p>
                    Today BLOMFREE &amp; CO. serves customers across the
                    country, and the measure of our progress has never changed:
                    the people we serve come back, and they bring others with
                    them.
                </p>
            </div>
        </Section>

        <!-- 3. CEO Spotlight -->
        <Section>
            <div class="grid gap-10 lg:grid-cols-5 lg:gap-14">
                <div v-reveal class="lg:col-span-2">
                    <div class="relative">
                        <div
                            class="absolute -left-5 -top-5 h-2/3 w-2/3 rounded-3xl bg-brand-orange/15"
                            aria-hidden="true"
                        />
                        <img
                            :src="ceoImage"
                            alt="Saturday Emomotimi Charles, CEO of BLOMFREE & CO."
                            class="relative aspect-[4/5] w-full rounded-3xl object-cover object-top shadow-xl"
                        />
                    </div>
                </div>

                <div v-reveal="120" class="flex flex-col gap-6 lg:col-span-3">
                    <div>
                        <Badge variant="info">CEO Spotlight</Badge>
                        <h2
                            class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                        >
                            Saturday Emomotimi Charles
                        </h2>
                        <p
                            class="mt-1 text-sm font-semibold uppercase tracking-wider text-brand-dark/50"
                        >
                            Founder &amp; Chief Executive Officer
                        </p>
                    </div>
                    <div
                        class="space-y-4 text-base leading-relaxed text-brand-dark/70"
                    >
                        <p>
                            Saturday Emomotimi Charles founded BLOMFREE &amp;
                            CO. on a belief that has never wavered: that
                            ordinary customers deserve the same standard of
                            quality and honesty that the privileged take for
                            granted.
                        </p>
                        <p>
                            His approach is hands-on. Each division of the group
                            carries his insistence on doing the groundwork
                            properly &mdash; verifying every plot, sourcing
                            every breed carefully, standing behind every product
                            sold.
                        </p>
                        <p>
                            That discipline is what turned a single venture into
                            a four-industry group without ever diluting what the
                            BLOMFREE name is meant to stand for.
                        </p>
                        <p>
                            He is equally deliberate about people. The team he
                            has built shares the same conviction, and customers
                            feel it in every interaction &mdash; before, during,
                            and long after a sale.
                        </p>
                        <p>
                            For him, the goal was never simply to grow. It was
                            to be trusted &mdash; and to stay that way.
                        </p>
                    </div>
                    <PullQuote cite="Saturday Emomotimi Charles, CEO">
                        Quality is Priceless
                    </PullQuote>
                </div>
            </div>
        </Section>

        <!-- 4. Mission / Vision / Values -->
        <Section>
            <div v-reveal class="max-w-2xl">
                <Badge variant="info">What Drives Us</Badge>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    Mission, vision &amp; values
                </h2>
            </div>
            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <Card
                    v-for="(principle, i) in principles"
                    :key="principle.title"
                    v-reveal="i * 90"
                    :hover="false"
                    class="flex flex-col gap-4 p-7"
                >
                    <span
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-orange/10 text-brand-orange"
                    >
                        <component :is="principle.icon" class="h-6 w-6" />
                    </span>
                    <h3 class="text-lg font-bold tracking-tight">
                        {{ principle.title }}
                    </h3>
                    <p class="text-sm leading-relaxed text-brand-dark/65">
                        {{ principle.body }}
                    </p>
                </Card>
            </div>
        </Section>

        <!-- 5. Four Pillars -->
        <section class="bg-brand-dark text-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div v-reveal class="max-w-2xl">
                    <span
                        class="inline-flex items-center rounded-full bg-brand-orange/15 px-2.5 py-0.5 text-xs font-semibold text-brand-orange"
                    >
                        The Four Pillars
                    </span>
                    <h2
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        Four pillars holding up one promise
                    </h2>
                    <p class="mt-4 text-base text-white/65">
                        Each subsidiary stands on its own &mdash; and together
                        they carry the BLOMFREE standard across every market we
                        serve.
                    </p>
                </div>
                <div
                    class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="(item, i) in subsidiaries"
                        :key="item.href"
                        v-reveal="i * 80"
                        :href="item.href"
                        class="group flex flex-col gap-4 rounded-2xl bg-white/5 p-6 ring-1 ring-white/10 transition-colors duration-300 hover:bg-white/10"
                    >
                        <span
                            class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-orange/15 text-brand-orange transition-colors duration-300 group-hover:bg-brand-orange group-hover:text-white"
                        >
                            <component :is="item.icon" class="h-6 w-6" />
                        </span>
                        <h3 class="text-base font-bold tracking-tight">
                            {{ item.name }}
                        </h3>
                        <p class="text-sm leading-relaxed text-white/60">
                            {{ item.tagline }}
                        </p>
                        <span
                            class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange"
                        >
                            Explore
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- 6. CTA band -->
        <CtaBand
            title="Partner with us"
            subtitle="Whether you are buying, selling, or just exploring — start the conversation with BLOMFREE & CO. today."
        />
    </AppLayout>
</template>

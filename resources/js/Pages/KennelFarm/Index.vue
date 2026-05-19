<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Dna, ShieldCheck, Syringe, LifeBuoy, HeartHandshake } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import AnimalCard from '@/Components/AnimalCard.vue';
import CtaBand from '@/Components/CtaBand.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import type { AnimalsByCategory, TabCategory } from '@/types/models';

const props = defineProps<{
    animalsByCategory: AnimalsByCategory;
}>();

interface Tab {
    key: TabCategory;
    label: string;
    hash: string;
}

const tabs: Tab[] = [
    { key: 'dog', label: 'Dogs', hash: 'dogs' },
    { key: 'cat', label: 'Cats', hash: 'cats' },
    { key: 'rabbit', label: 'Rabbits', hash: 'rabbits' },
    { key: 'grasscutter', label: 'Grasscutters', hash: 'grasscutters' },
];

const activeTab = ref<TabCategory>('dog');

onMounted(() => {
    const hash = window.location.hash.replace('#', '');
    const matched = tabs.find((tab) => tab.hash === hash);
    if (matched) {
        activeTab.value = matched.key;
    }
});

function selectTab(tab: Tab): void {
    activeTab.value = tab.key;
    history.replaceState(null, '', `#${tab.hash}`);
}

const activeAnimals = computed(() => props.animalsByCategory[activeTab.value] ?? []);

const reasons = [
    {
        icon: Dna,
        title: 'Imported genetics',
        body: 'Breeding stock sourced from established, traceable bloodlines.',
    },
    {
        icon: ShieldCheck,
        title: 'Health-certified',
        body: 'Every animal is vet-checked before it is listed for sale.',
    },
    {
        icon: Syringe,
        title: 'Vaccinations on record',
        body: 'Clear vaccination and deworming history you can verify.',
    },
    {
        icon: LifeBuoy,
        title: 'Ongoing support',
        body: 'Guidance on feeding, housing and care after you take delivery.',
    },
    {
        icon: HeartHandshake,
        title: 'Ethical breeding',
        body: 'Animals are raised in clean, humane, well-managed conditions.',
    },
];
</script>

<template>
    <SeoMeta
        title="BLOMFREE Kennel & Farm — Premium breeds"
        description="Dogs, cats, rabbits and grasscutters — imported and locally bred to a high standard, health-checked and ethically kept."
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
                <Badge variant="info">BLOMFREE Kennel &amp; Farm</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl"
                >
                    Premium Breeds. Trusted Source.
                </h1>
            </template>
            <template #subtitle>
                Imported and locally bred animals raised to a high standard —
                health-checked, ethically kept, and ready for a good home or
                farm.
            </template>
        </Hero>

        <Section>
            <!-- Category tabs -->
            <div
                class="flex flex-wrap gap-2 border-b border-brand-dark/10 pb-1"
                role="tablist"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.key"
                    class="rounded-t-xl border-b-2 px-4 py-2.5 text-sm font-bold tracking-tight transition-colors"
                    :class="
                        activeTab === tab.key
                            ? 'border-brand-orange text-brand-orange'
                            : 'border-transparent text-brand-dark/55 hover:text-brand-dark'
                    "
                    @click="selectTab(tab)"
                >
                    {{ tab.label }}
                    <span class="ml-1 text-xs font-semibold text-brand-dark/35">
                        {{ animalsByCategory[tab.key]?.length ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Grid -->
            <div
                v-if="activeAnimals.length"
                class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <AnimalCard
                    v-for="animal in activeAnimals"
                    :key="animal.id"
                    :animal="animal"
                />
            </div>
            <div
                v-else
                class="mt-8 flex flex-col items-center justify-center gap-2 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-16 text-center"
            >
                <p class="text-lg font-bold tracking-tight">
                    Nothing listed here yet
                </p>
                <p class="max-w-sm text-sm text-brand-dark/55">
                    New animals in this category are added regularly — check
                    back soon or send us an inquiry.
                </p>
            </div>
        </Section>

        <!-- Why choose us -->
        <section class="bg-brand-dark text-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div class="max-w-2xl">
                    <span
                        class="inline-flex items-center rounded-full bg-brand-orange/15 px-2.5 py-0.5 text-xs font-semibold text-brand-orange"
                    >
                        Why Choose Us
                    </span>
                    <h2
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        Animals you can trust, from a source you can verify
                    </h2>
                </div>
                <div
                    class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="reason in reasons"
                        :key="reason.title"
                        class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10"
                    >
                        <span
                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-orange/15 text-brand-orange"
                        >
                            <component :is="reason.icon" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold tracking-tight">
                            {{ reason.title }}
                        </h3>
                        <p
                            class="mt-1.5 text-sm leading-relaxed text-white/60"
                        >
                            {{ reason.body }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Inquiry CTA -->
        <CtaBand
            title="Looking for a breed we haven't listed?"
            subtitle="Tell us what you need — dogs, cats, rabbits or grasscutters — and our team will source it for you."
        />
    </AppLayout>
</template>

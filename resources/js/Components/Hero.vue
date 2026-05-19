<script setup lang="ts">
import { computed, useSlots } from 'vue';

type Align = 'left' | 'center';

const props = withDefaults(
    defineProps<{
        bgImage?: string;
        overlay?: boolean;
        align?: Align;
        minHeightClass?: string;
    }>(),
    {
        bgImage: undefined,
        overlay: true,
        align: 'left',
        minHeightClass: 'min-h-[70vh]',
    },
);

const slots = useSlots();

const bgStyle = computed(() =>
    props.bgImage ? { backgroundImage: `url('${props.bgImage}')` } : undefined,
);

const contentAlign = computed(() =>
    props.align === 'center'
        ? 'mx-auto items-center text-center'
        : 'items-start text-left',
);
</script>

<template>
    <section
        class="relative isolate flex w-full items-center overflow-hidden bg-brand-dark bg-cover bg-center"
        :class="minHeightClass"
        :style="bgStyle"
    >
        <!-- Gradient overlays -->
        <div
            v-if="overlay"
            class="absolute inset-0 bg-gradient-to-r from-brand-dark/90 via-brand-dark/70 to-brand-dark/20"
            aria-hidden="true"
        />
        <div
            v-if="overlay"
            class="absolute inset-0 bg-gradient-to-t from-brand-dark/80 via-transparent to-transparent"
            aria-hidden="true"
        />
        <slot name="overlay" />

        <div
            class="relative mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8 lg:py-32"
        >
            <div class="flex max-w-2xl flex-col gap-6" :class="contentAlign">
                <div v-if="slots.eyebrow">
                    <slot name="eyebrow" />
                </div>
                <slot name="title" />
                <div
                    v-if="slots.subtitle"
                    class="text-base text-white/80 sm:text-lg"
                >
                    <slot name="subtitle" />
                </div>
                <div
                    v-if="slots.actions"
                    class="mt-2 flex flex-wrap gap-3"
                    :class="align === 'center' ? 'justify-center' : ''"
                >
                    <slot name="actions" />
                </div>
                <slot />
            </div>
        </div>
    </section>
</template>

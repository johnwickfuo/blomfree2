<script setup lang="ts">
import { computed } from 'vue';

type Width = 'default' | 'narrow' | 'wide' | 'full';

const props = withDefaults(
    defineProps<{
        as?: string;
        width?: Width;
        padded?: boolean;
    }>(),
    {
        as: 'section',
        width: 'default',
        padded: true,
    },
);

const widths: Record<Width, string> = {
    default: 'max-w-7xl',
    narrow: 'max-w-3xl',
    wide: 'max-w-screen-2xl',
    full: 'max-w-none',
};

const classes = computed(() => [
    'mx-auto w-full px-4 sm:px-6 lg:px-8',
    widths[props.width],
    props.padded ? 'py-12 sm:py-16 lg:py-20' : '',
]);
</script>

<template>
    <component :is="as" :class="classes">
        <slot />
    </component>
</template>

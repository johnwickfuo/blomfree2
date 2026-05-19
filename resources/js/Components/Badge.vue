<script setup lang="ts">
import { computed } from 'vue';

type Variant = 'success' | 'warning' | 'info' | 'neutral';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
    }>(),
    {
        variant: 'neutral',
    },
);

const variants: Record<Variant, string> = {
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    info: 'bg-orange-100 text-brand-orangeDark',
    neutral: 'bg-gray-100 text-gray-700',
};

const classes = computed(() => [
    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold',
    variants[props.variant],
]);
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>

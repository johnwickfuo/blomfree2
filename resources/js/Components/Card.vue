<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        as?: string;
        hover?: boolean;
    }>(),
    {
        as: 'div',
        hover: true,
    },
);

const classes = computed(() => [
    'rounded-2xl bg-white shadow-md ring-1 ring-black/5 transition duration-300',
    props.hover ? 'hover:-translate-y-1 hover:shadow-xl' : '',
]);
</script>

<template>
    <component :is="as" :class="classes">
        <slot />
    </component>
</template>

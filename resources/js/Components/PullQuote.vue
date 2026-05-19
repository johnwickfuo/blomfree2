<script setup lang="ts">
withDefaults(
    defineProps<{
        cite?: string;
    }>(),
    {
        cite: undefined,
    },
);
</script>

<template>
    <figure class="relative">
        <span
            class="pointer-events-none absolute -left-2 -top-6 select-none font-extrabold leading-none text-brand-orange/20"
            style="font-size: 5rem"
            aria-hidden="true"
        >
            &ldquo;
        </span>
        <blockquote
            class="relative border-l-4 border-brand-orange pl-5 text-2xl font-extrabold tracking-tight text-brand-dark sm:text-3xl"
        >
            <slot />
        </blockquote>
        <figcaption
            v-if="cite"
            class="mt-3 pl-5 text-sm font-semibold uppercase tracking-wider text-brand-dark/50"
        >
            {{ cite }}
        </figcaption>
    </figure>
</template>

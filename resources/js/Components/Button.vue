<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost';
type Size = 'sm' | 'md' | 'lg';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        size?: Size;
        href?: string;
        external?: boolean;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        href: undefined,
        external: false,
        type: 'button',
        disabled: false,
    },
);

const variants: Record<Variant, string> = {
    primary:
        'bg-brand-orange text-white shadow-sm hover:bg-brand-orangeDark focus-visible:ring-brand-orange',
    secondary:
        'bg-brand-dark text-white shadow-sm hover:bg-black focus-visible:ring-brand-dark',
    outline:
        'border-2 border-brand-orange text-brand-orange hover:bg-brand-orange hover:text-white focus-visible:ring-brand-orange',
    ghost: 'text-brand-dark hover:bg-brand-cream focus-visible:ring-brand-orange',
};

const sizes: Record<Size, string> = {
    sm: 'gap-1.5 px-4 py-2 text-sm',
    md: 'gap-2 px-6 py-3 text-sm',
    lg: 'gap-2.5 px-8 py-4 text-base',
};

const classes = computed(() => [
    'inline-flex items-center justify-center rounded-full font-semibold tracking-tight transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
    variants[props.variant],
    sizes[props.size],
]);

const component = computed(() => {
    if (!props.href) return 'button';
    return props.external ? 'a' : Link;
});
</script>

<template>
    <component
        :is="component"
        :class="classes"
        :href="href"
        :type="href ? undefined : type"
        :disabled="href ? undefined : disabled"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener noreferrer' : undefined"
    >
        <slot />
    </component>
</template>

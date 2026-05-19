<script setup lang="ts">
import type { Component } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';

defineProps<{
    name: string;
    tagline: string;
    href: string;
    icon: Component;
}>();
</script>

<template>
    <Card class="group flex h-full flex-col gap-5 p-6 sm:p-7">
        <span
            class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-orange/10 text-brand-orange transition-colors duration-300 group-hover:bg-brand-orange group-hover:text-white"
        >
            <component :is="icon" class="h-6 w-6" />
        </span>

        <div>
            <h3 class="text-lg font-bold tracking-tight">{{ name }}</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-brand-dark/65">
                {{ tagline }}
            </p>
        </div>

        <Link
            :href="href"
            class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange transition-colors hover:text-brand-orangeDark"
        >
            Explore
            <ArrowRight
                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
            />
        </Link>
    </Card>
</template>

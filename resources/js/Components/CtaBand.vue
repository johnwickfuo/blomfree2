<script setup lang="ts">
import { Phone } from 'lucide-vue-next';
import Button from '@/Components/Button.vue';

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
    }>(),
    {
        subtitle: undefined,
    },
);

const whatsappUrl = 'https://wa.me/2348103965317';
const telUrl = 'tel:+2348103965317';
</script>

<template>
    <section class="bg-brand-dark text-white">
        <div
            class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8"
        >
            <h2
                v-reveal
                class="text-3xl font-extrabold tracking-tight sm:text-4xl"
            >
                {{ title }}
            </h2>
            <p
                v-if="subtitle"
                v-reveal="80"
                class="mx-auto mt-4 max-w-xl text-base text-white/70"
            >
                {{ subtitle }}
            </p>
            <div
                v-reveal="160"
                class="mt-8 flex flex-wrap justify-center gap-3"
            >
                <Button :href="whatsappUrl" external size="lg">
                    <svg
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="h-4 w-4"
                        aria-hidden="true"
                    >
                        <path
                            d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.01c-.24.68-1.42 1.3-1.95 1.34-.5.05-.96.24-3.23-.67-2.73-1.08-4.45-3.88-4.58-4.06-.13-.18-1.1-1.47-1.1-2.8 0-1.33.7-1.98.94-2.25.24-.27.53-.34.71-.34.18 0 .35 0 .51.01.16.01.39-.06.6.46.24.58.79 2 .86 2.14.07.14.12.31.02.49-.09.18-.14.29-.27.45-.13.16-.28.35-.4.47-.13.13-.27.28-.12.54.15.27.66 1.09 1.42 1.77.97.87 1.79 1.14 2.05 1.27.26.13.41.11.56-.07.15-.18.65-.76.82-1.02.17-.26.35-.22.59-.13.24.09 1.51.71 1.77.84.26.13.43.2.49.31.06.11.06.66-.18 1.34z"
                        />
                    </svg>
                    Chat on WhatsApp
                </Button>
                <Button :href="telUrl" external variant="outline" size="lg">
                    <Phone class="h-4 w-4" />
                    Call Us
                </Button>
            </div>
        </div>
    </section>
</template>

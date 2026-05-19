<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Package } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { formatNaira } from '@/lib/format';
import type { Animal } from '@/types/models';

const props = defineProps<{
    animal: Animal;
}>();

const isPool = computed(() => props.animal.listing_type === 'pool');

const availabilityBadge = computed<{
    label: string;
    variant: 'success' | 'warning' | 'neutral';
}>(() => {
    switch (props.animal.effective_availability) {
        case 'available':
            return { label: 'Available', variant: 'success' };
        case 'reserved':
            return { label: 'Reserved', variant: 'warning' };
        case 'on_order':
            return { label: 'On Order', variant: 'warning' };
        default:
            return { label: 'Sold', variant: 'neutral' };
    }
});

const sexLabel = computed<string | null>(() => {
    if (props.animal.sex === 'male') return 'Male';
    if (props.animal.sex === 'female') return 'Female';
    return null;
});
</script>

<template>
    <Card class="flex flex-col overflow-hidden">
        <Link :href="`/kennel-farm/${animal.slug}`" class="block">
            <div class="aspect-[4/3] w-full bg-brand-dark/5">
                <img
                    v-if="animal.cover_url"
                    :src="animal.cover_url"
                    :alt="animal.name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover"
                />
            </div>
        </Link>

        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex flex-wrap gap-1.5">
                <Badge :variant="availabilityBadge.variant">
                    {{ availabilityBadge.label }}
                </Badge>
                <Badge v-if="isPool" variant="info">
                    <Package class="h-3 w-3" />
                    {{ animal.stock }} available
                </Badge>
                <Badge v-else-if="sexLabel" variant="info">
                    {{ sexLabel }}
                </Badge>
                <Badge v-if="!isPool && animal.age_text" variant="neutral">
                    {{ animal.age_text }}
                </Badge>
            </div>

            <Link
                :href="`/kennel-farm/${animal.slug}`"
                class="text-lg font-bold tracking-tight transition-colors hover:text-brand-orange"
            >
                {{ animal.name }}
            </Link>
            <p class="-mt-2 text-sm text-brand-dark/60">
                {{ animal.breed }}
            </p>

            <div class="mt-auto flex items-end justify-between pt-3">
                <div>
                    <p class="text-xs text-brand-dark/50">
                        {{ isPool ? 'Price each' : 'Price' }}
                    </p>
                    <p
                        class="text-lg font-extrabold tracking-tight text-brand-orange"
                    >
                        {{ formatNaira(animal.price) }}
                    </p>
                </div>
                <Link
                    :href="`/kennel-farm/${animal.slug}`"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange hover:text-brand-orangeDark"
                >
                    Inspect or Buy
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </Card>
</template>

<script setup lang="ts">
import { computed, watch, onBeforeUnmount } from 'vue';
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    images: string[];
    modelValue: number | null;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const isOpen = computed(() => props.modelValue !== null);
const current = computed(() =>
    props.modelValue !== null ? props.images[props.modelValue] : null,
);

const close = (): void => emit('update:modelValue', null);

const prev = (): void => {
    if (props.modelValue === null) return;
    emit(
        'update:modelValue',
        (props.modelValue - 1 + props.images.length) % props.images.length,
    );
};

const next = (): void => {
    if (props.modelValue === null) return;
    emit('update:modelValue', (props.modelValue + 1) % props.images.length);
};

const onKey = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') prev();
    if (event.key === 'ArrowRight') next();
};

watch(isOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
        window.addEventListener('keydown', onKey);
    } else {
        window.removeEventListener('keydown', onKey);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="lb-fade">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4 sm:p-8"
                @click.self="close"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                    aria-label="Close gallery"
                    @click="close"
                >
                    <X class="h-5 w-5" />
                </button>

                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute left-3 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 sm:left-6"
                    aria-label="Previous image"
                    @click="prev"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>

                <img
                    v-if="current"
                    :src="current"
                    alt=""
                    class="max-h-[85vh] max-w-full rounded-lg object-contain"
                />

                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute right-3 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 sm:right-6"
                    aria-label="Next image"
                    @click="next"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>

                <div
                    v-if="images.length > 1"
                    class="absolute bottom-5 left-1/2 -translate-x-1/2 text-sm font-medium text-white/70"
                >
                    {{ (modelValue ?? 0) + 1 }} / {{ images.length }}
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lb-fade-enter-active,
.lb-fade-leave-active {
    transition: opacity 0.2s ease;
}

.lb-fade-enter-from,
.lb-fade-leave-to {
    opacity: 0;
}
</style>

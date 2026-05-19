<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    schema: Record<string, unknown>;
}>();

const tag = ref<HTMLScriptElement | null>(null);

onMounted(() => {
    const el = document.createElement('script');
    el.type = 'application/ld+json';
    el.text = JSON.stringify(props.schema);
    document.head.appendChild(el);
    tag.value = el;
});

onBeforeUnmount(() => {
    tag.value?.remove();
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>

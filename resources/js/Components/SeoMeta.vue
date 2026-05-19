<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        image?: string;
        url?: string;
        type?: string;
    }>(),
    {
        description:
            'BLOMFREE & CO. NIG. LTD — your trusted Nigerian partner for land, premium livestock, fashion and gadgets.',
        image: '/og-default.jpg',
        url: undefined,
        type: 'website',
    },
);

const origin = ref<string>('');

onMounted(() => {
    origin.value = window.location.origin;
});

const absoluteImage = computed(() => {
    if (props.image.startsWith('http')) {
        return props.image;
    }
    return origin.value ? origin.value + props.image : props.image;
});

const canonical = computed(() => {
    if (props.url) {
        return props.url.startsWith('http')
            ? props.url
            : origin.value + props.url;
    }
    if (typeof window === 'undefined') {
        return '';
    }
    return window.location.origin + window.location.pathname;
});
</script>

<template>
    <Head :title="title">
        <meta head-key="description" name="description" :content="description" />
        <link head-key="canonical" rel="canonical" :href="canonical" />
        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta
            head-key="og:description"
            property="og:description"
            :content="description"
        />
        <meta
            head-key="og:image"
            property="og:image"
            :content="absoluteImage"
        />
        <meta head-key="og:url" property="og:url" :content="canonical" />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            content="summary_large_image"
        />
        <meta
            head-key="twitter:title"
            name="twitter:title"
            :content="title"
        />
        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="description"
        />
        <meta
            head-key="twitter:image"
            name="twitter:image"
            :content="absoluteImage"
        />
    </Head>
</template>

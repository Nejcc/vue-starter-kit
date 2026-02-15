<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SeoSharedProps } from '@/types';

interface Props {
    title: string;
    description?: string;
    image?: string;
    canonicalUrl?: string;
    type?: string;
    twitterCard?: 'summary' | 'summary_large_image';
    robots?: string;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    image: '',
    canonicalUrl: '',
    type: 'website',
    twitterCard: 'summary',
    robots: '',
});

const page = usePage();
const seo = computed(() => (page.props.seo as SeoSharedProps) ?? {});

const metaDescription = computed(
    () => props.description || seo.value.defaultDescription || '',
);

const canonical = computed(() => {
    if (props.canonicalUrl) {
        return props.canonicalUrl;
    }
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    return origin + page.url;
});

const siteName = computed(() => seo.value.siteName || '');
</script>

<template>
    <Head :title="title">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <meta v-if="robots" head-key="robots" name="robots" :content="robots" />

        <!-- Open Graph -->
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta
            v-if="metaDescription"
            head-key="og:description"
            property="og:description"
            :content="metaDescription"
        />
        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:url" property="og:url" :content="canonical" />
        <meta
            v-if="siteName"
            head-key="og:site_name"
            property="og:site_name"
            :content="siteName"
        />
        <meta
            v-if="image"
            head-key="og:image"
            property="og:image"
            :content="image"
        />

        <!-- Twitter Card -->
        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="twitterCard"
        />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta
            v-if="metaDescription"
            head-key="twitter:description"
            name="twitter:description"
            :content="metaDescription"
        />
        <meta
            v-if="image"
            head-key="twitter:image"
            name="twitter:image"
            :content="image"
        />

        <!-- Canonical URL -->
        <link head-key="canonical" rel="canonical" :href="canonical" />

        <slot />
    </Head>
</template>

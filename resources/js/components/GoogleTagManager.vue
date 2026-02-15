<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useGoogleTagManager } from '@/composables/useGoogleTagManager';
import type { SeoSharedProps } from '@/types';

const page = usePage();
const { initialize } = useGoogleTagManager();

onMounted(() => {
    const seo = page.props.seo as SeoSharedProps | undefined;
    if (seo?.gtmId) {
        initialize(seo.gtmId);
    }
});
</script>

<template>
    <span />
</template>

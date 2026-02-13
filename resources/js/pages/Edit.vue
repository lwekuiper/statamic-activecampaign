<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    title: String,
    action: String,
    deleteUrl: String,
    listingUrl: String,
    blueprint: Object,
    values: Object,
    meta: Object,
    localizations: Array,
    site: String,
    hasOrigin: Boolean,
    originValues: Object,
    originMeta: Object,
    localizedFields: Array,
    configureUrl: String,
});
</script>

<template>
    <Head :title="title" />
    <activecampaign-publish-form
        :title="title"
        :initial-action="action"
        :initial-delete-url="deleteUrl"
        :initial-listing-url="listingUrl"
        :blueprint="blueprint"
        :initial-meta="meta"
        :initial-values="values"
        :initial-localizations="localizations"
        :initial-site="site"
        :initial-has-origin="hasOrigin"
        :initial-origin-values="originValues"
        :initial-origin-meta="originMeta"
        :initial-localized-fields="localizedFields"
        :initial-configure-url="configureUrl"
    />
</template>

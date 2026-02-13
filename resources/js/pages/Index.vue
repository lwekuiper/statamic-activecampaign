<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    createFormUrl: String,
    configureUrl: String,
    formConfigs: Array,
    localizations: Array,
    site: String,
});
</script>

<template>
    <Head :title="__('ActiveCampaign')" />
    <activecampaign-listing
        :create-form-url="createFormUrl"
        :configure-url="configureUrl"
        :initial-form-configs="formConfigs"
        :initial-localizations="localizations"
        :initial-site="site"
    />
</template>

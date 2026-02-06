<script setup>
import { Head, useArchitecturalBackground } from '@statamic/cms/inertia';
import { EmptyStateMenu, EmptyStateItem } from '@statamic/cms/ui';

defineProps({
    createUrl: String,
});

useArchitecturalBackground();
</script>

<template>
    <Head :title="__('ActiveCampaign')" />

    <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <svg viewBox="0 0 124 124" xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-500"><path fill="currentColor" d="m99.5 61.8-64.8 43c-3 2-4.5 5.2-4.5 8.5V124l78.5-51.5c3.5-2.5 5.8-6.5 5.8-10.7s-2-8.3-5.8-10.8L30.2 0v10c0 3.5 1.8 6.8 4.5 8.5l64.8 43.3Z"/><path fill="currentColor" d="M60.6 65.2c3.5 2.2 8 2.2 11.4 0l5.5-3.7-40.8-27.6c-2.5-1.7-6.2 0-6.2 3.2v8.2l21.1 14.2 8.9 5.7Z"/></svg>
            <span v-text="__('ActiveCampaign')" />
        </h1>
    </header>

    <EmptyStateMenu :heading="__('Connect a Statamic form to an ActiveCampaign list and map your fields.')">
        <EmptyStateItem
            :href="createUrl"
            icon="forms"
            :heading="__('Create Form')"
            :description="__('Get started with ActiveCampaign integration by creating a Statamic form first.')"
        />
    </EmptyStateMenu>
</template>

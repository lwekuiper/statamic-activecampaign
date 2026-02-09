<script setup>
import { Header, Listing, DropdownItem } from '@statamic/cms/ui';
import { Link } from '@statamic/cms/inertia';
import SiteSelector from '../SiteSelector.vue';
import { ref } from 'vue';
import axios from 'axios';
import ActiveCampaignIcon from '../../../svg/activecampaign.svg?raw';

const props = defineProps({
    createFormUrl: { type: String, required: true },
    initialFormConfigs: { type: Array, required: true },
    initialLocalizations: { type: Array, required: true },
    initialSite: { type: String, required: true },
});

const rows = ref(props.initialFormConfigs);
const localizations = ref(props.initialLocalizations);
const site = ref(props.initialSite);
const loading = ref(false);

const columns = [
    { field: 'form', label: __('Form'), visible: true },
    { field: 'list_ids', label: __('List IDs'), visible: true },
    { field: 'tag_ids', label: __('Tag IDs'), visible: true },
];

function localizationSelected(handle) {
    const localization = localizations.value.find(l => l.handle === handle);
    if (!localization || localization.active) return;

    loading.value = true;

    axios.get(localization.url).then(response => {
        rows.value = response.data.formConfigs;
        localizations.value = response.data.localizations;
        site.value = localization.handle;
        loading.value = false;
    });
}

</script>

<template>
    <div class="max-w-5xl mx-auto">
        <Header :title="__('ActiveCampaign')" :icon="ActiveCampaignIcon">
            <SiteSelector
                v-if="localizations.length > 1"
                :sites="localizations"
                :model-value="site"
                @update:model-value="localizationSelected"
            />

            <ui-button
                :href="createFormUrl"
                :text="__('Create Form')"
                variant="primary"
            />
        </Header>

        <Listing
            v-if="!loading"
            :items="rows"
            :columns="columns"
            :allow-presets="false"
            :allow-customizing-columns="false"
            :allow-search="false"
            preferences-prefix="activecampaign"
        >
            <template #cell-form="{ row: form }">
                <Link :href="form.edit_url" class="flex items-center gap-2">
                    {{ form.title }}
                </Link>
            </template>
            <template #cell-list_ids="{ row: form }">
                {{ form.list_ids?.join(', ') }}
            </template>
            <template #cell-tag_ids="{ row: form }">
                {{ form.tag_ids?.join(', ') }}
            </template>
            <template #prepended-row-actions="{ row: form }">
                <DropdownItem
                    :text="__('Edit')"
                    :href="form.edit_url"
                    icon="edit"
                />
            </template>
        </Listing>

        <div v-else class="card p-4 text-center text-gray-500">
            {{ __('Loading...') }}
        </div>
    </div>
</template>

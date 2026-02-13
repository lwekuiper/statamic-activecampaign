<script setup>
import { Header, Listing, Dropdown, DropdownMenu, DropdownItem, StatusIndicator } from '@statamic/cms/ui';
import { Link } from '@statamic/cms/inertia';
import SiteSelector from '../SiteSelector.vue';
import { ref } from 'vue';
import axios from 'axios';
import ActiveCampaignIcon from '../../../svg/activecampaign.svg?raw';

const props = defineProps({
    createFormUrl: { type: String, required: true },
    configureUrl: { type: String, default: null },
    initialFormConfigs: { type: Array, required: true },
    initialLocalizations: { type: Array, required: true },
    initialSite: { type: String, required: true },
});

const rows = ref(props.initialFormConfigs);
const localizations = ref(props.initialLocalizations);
const site = ref(props.initialSite);
const loading = ref(false);

const columns = [
    { field: 'title', label: __('Form'), visible: true },
    { field: 'lists', label: __('Lists'), visible: true },
    { field: 'tags', label: __('Tags'), visible: true },
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
    }).catch(() => {
        loading.value = false;
        Statamic.$toast.error(__('Something went wrong'));
    });
}

function deleteRow(form) {
    if (!confirm(__('Are you sure?'))) return;
    axios.delete(form.delete_url)
        .then(() => rows.value = rows.value.filter(r => r !== form))
        .catch(() => Statamic.$toast.error(__('Something went wrong')));
}

</script>

<template>
    <div class="max-w-5xl mx-auto">
        <Header :title="__('ActiveCampaign')" :icon="ActiveCampaignIcon">
            <Dropdown v-if="configureUrl" placement="left-start">
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Configure')"
                        icon="cog"
                        :href="configureUrl"
                    />
                </DropdownMenu>
            </Dropdown>

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
            <template #cell-title="{ row: form }">
                <Link :href="form.edit_url" class="inline-flex items-center gap-2">
                    <StatusIndicator :status="form.status" />
                    <span>{{ form.title }}</span>
                </Link>
            </template>
            <template #cell-lists="{ row: form }">
                {{ form.lists || '' }}
            </template>
            <template #cell-tags="{ row: form }">
                {{ form.tags || '' }}
            </template>
            <template #prepended-row-actions="{ row: form }">
                <DropdownItem
                    :text="__('Edit')"
                    :href="form.edit_url"
                    icon="edit"
                />
                <DropdownItem
                    v-if="form.delete_url"
                    :text="__('Delete')"
                    icon="trash"
                    class="warning"
                    @click="deleteRow(form)"
                />
            </template>
        </Listing>

        <div v-else class="card p-4 text-center text-gray-500">
            {{ __('Loading...') }}
        </div>
    </div>
</template>

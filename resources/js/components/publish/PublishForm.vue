<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import {
    Header,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    PublishContainer,
    PublishTabs
} from '@statamic/cms/ui';
import SiteSelector from '../SiteSelector.vue';

const props = defineProps({
    title: String,
    initialAction: String,
    initialDeleteUrl: String,
    initialListingUrl: String,
    blueprint: Object,
    initialMeta: Object,
    initialValues: Object,
    initialLocalizations: Array,
    initialSite: String,
});

const container = ref(null);
const deleter = ref(null);
const localizing = ref(false);
const action = ref(props.initialAction);
const deleteUrl = ref(props.initialDeleteUrl);
const listingUrl = ref(props.initialListingUrl);
const meta = ref(props.initialMeta);
const values = ref(props.initialValues);
const localizations = ref(props.initialLocalizations);
const site = ref(props.initialSite);
const error = ref(null);
const errors = ref({});
const saving = ref(false);

const isDirty = computed(() => Statamic.$dirty.has('base'));

function clearErrors() {
    error.value = null;
    errors.value = {};
}

function save() {
    if (!action.value) return;

    saving.value = true;
    clearErrors();

    axios.patch(action.value, values.value).then(() => {
        saving.value = false;
        Statamic.$toast.success(__('Saved'));
        container.value.saved();
    }).catch(e => handleAxiosError(e));
}

function handleAxiosError(e) {
    saving.value = false;
    if (e.response && e.response.status === 422) {
        const { message, errors: responseErrors } = e.response.data;
        error.value = message;
        errors.value = responseErrors;
        Statamic.$toast.error(message);
    } else {
        const message = data_get(e, 'response.data.message');
        Statamic.$toast.error(message || e);
        console.log(e);
    }
}

function localizationSelected(handle) {
    const localization = localizations.value.find(l => l.handle === handle);
    if (!localization || localization.active) return;

    if (isDirty.value) {
        if (!confirm(__('Are you sure? Unsaved changes will be lost.'))) {
            return;
        }
    }

    localizing.value = localization.handle;
    window.history.replaceState({}, '', localization.url);

    axios.get(localization.url).then(response => {
        const data = response.data;
        action.value = data.action;
        deleteUrl.value = data.deleteUrl;
        listingUrl.value = data.listingUrl;
        values.value = data.values;
        meta.value = data.meta;
        localizations.value = data.localizations;
        site.value = localization.handle;
        localizing.value = false;
        container.value.clearDirtyState();
    });
}

// Global keyboard shortcut
Statamic.$keys.bindGlobal(['mod+s'], e => {
    e.preventDefault();
    save();
});
</script>

<template>
    <div>
        <Header :title="title" icon="forms">
            <Dropdown v-if="deleteUrl">
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Delete Config')"
                        variant="destructive"
                        @click="deleter.confirm()"
                    />
                </DropdownMenu>
            </Dropdown>

            <resource-deleter
                ref="deleter"
                :resource-title="title"
                :route="deleteUrl"
                :redirect="listingUrl"
            />

            <SiteSelector
                v-if="localizations.length > 1"
                :sites="localizations"
                :model-value="site"
                @update:model-value="localizationSelected"
            />

            <ui-button
                variant="primary"
                :text="__('Save')"
                @click="save"
            />
        </Header>

        <PublishContainer
            ref="container"
            name="base"
            :blueprint="blueprint"
            :meta="meta"
            :errors="errors"
            v-model="values"
            v-slot="{ setFieldValue, setFieldMeta }"
        >
            <PublishTabs
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
            />
        </PublishContainer>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue';
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
    initialHasOrigin: Boolean,
    initialOriginValues: Object,
    initialOriginMeta: Object,
    initialLocalizedFields: Array,
    initialConfigureUrl: String,
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
const hasOrigin = ref(props.initialHasOrigin);
const originValues = ref(props.initialOriginValues);
const originMeta = ref(props.initialOriginMeta);
const localizedFields = ref(props.initialLocalizedFields ?? []);
const configureUrl = ref(props.initialConfigureUrl);
const error = ref(null);
const errors = ref({});
const saving = ref(false);
const pendingLocalization = ref(null);

const isDirty = computed(() => Statamic.$dirty.has('base'));

function clearErrors() {
    error.value = null;
    errors.value = {};
}

function save() {
    if (!action.value) return;

    saving.value = true;
    clearErrors();

    const payload = { ...values.value };

    if (hasOrigin.value) {
        payload._localized = localizedFields.value;
    }

    axios.patch(action.value, payload).then(() => {
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
        pendingLocalization.value = localization;
        return;
    }

    switchToLocalization(localization);
}

function confirmSwitchLocalization() {
    switchToLocalization(pendingLocalization.value);
    pendingLocalization.value = null;
}

function switchToLocalization(localization) {
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
        hasOrigin.value = data.hasOrigin ?? false;
        originValues.value = data.originValues ?? null;
        originMeta.value = data.originMeta ?? null;
        localizedFields.value = data.localizedFields ?? [];
        configureUrl.value = data.configureUrl ?? null;
        site.value = localization.handle;
        localizing.value = false;
        nextTick(() => container.value.clearDirtyState());
    });
}

// Progress bar during save
watch(saving, (val) => Statamic.$progress.loading('activecampaign-publish-form', val));

// Global keyboard shortcut
const saveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], e => {
    e.preventDefault();
    save();
});
onUnmounted(() => saveKeyBinding.destroy());
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
            :origin-values="originValues"
            :origin-meta="originMeta"
            v-model="values"
            v-model:modified-fields="localizedFields"
            v-slot="{ setFieldValue, setFieldMeta }"
        >
            <PublishTabs
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
            />
        </PublishContainer>

        <confirmation-modal
            :open="pendingLocalization"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Continue')"
            :danger="true"
            @confirm="confirmSwitchLocalization"
            @cancel="pendingLocalization = null"
        />
    </div>
</template>

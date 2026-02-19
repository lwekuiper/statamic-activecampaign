<template>
    <div>

        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list v-if="deleteUrl" class="rtl:ml-2 ltr:mr-2">
                    <dropdown-item
                        :text="__('Delete Config')"
                        class="warning"
                        @click="$refs.deleter.confirm()"
                    >
                        <resource-deleter
                            ref="deleter"
                            :resourceTitle="title"
                            :route="deleteUrl"
                            :redirect="listingUrl">
                        </resource-deleter>
                    </dropdown-item>
                </dropdown-list>

                <site-selector
                    v-if="localizations.length > 1"
                    class="rtl:ml-4 ltr:mr-4"
                    :sites="localizations"
                    :value="site"
                    @input="localizationSelected"
                />

                <button
                    class="btn-primary min-w-100"
                    :class="{ 'opacity-25': !canSave }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="__('Save')" />
            </div>
        </header>

        <publish-container
            ref="container"
            name="base"
            :blueprint="blueprint"
            :meta="meta"
            :errors="errors"
            :localized-fields="localizedFields"
            :is-root="!hasOrigin"
            v-model="values"
            v-slot="{ setFieldMeta }"
        >
            <publish-tabs
                :syncable="hasOrigin"
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
                @synced="syncField"
                @desynced="desyncField" />
        </publish-container>

    </div>
</template>

<script>
import SiteSelector from '../../../../vendor/statamic/cms/resources/js/components/SiteSelector.vue';

export default {

    components: {SiteSelector},

    props: {
        title: String,
        initialAction: String,
        initialDeleteUrl: String,
        initialListingUrl: String,
        blueprint: Object,
        initialMeta: Object,
        initialValues: Object,
        initialLocalizations: Array,
        initialSite: String,
        initialHasOrigin: { type: Boolean, default: false },
        initialOriginValues: { type: Object, default: null },
        initialOriginMeta: { type: Object, default: null },
        initialLocalizedFields: { type: Array, default: () => [] },
    },

    data() {
        return {
            localizing: false,
            action: this.initialAction,
            deleteUrl: this.initialDeleteUrl,
            listingUrl: this.initialListingUrl,
            meta: _.clone(this.initialMeta),
            values: _.clone(this.initialValues),
            localizations: _.clone(this.initialLocalizations),
            site: this.initialSite,
            hasOrigin: this.initialHasOrigin,
            originValues: this.initialOriginValues || {},
            originMeta: this.initialOriginMeta || {},
            localizedFields: this.initialLocalizedFields,
            saving: false,
            error: null,
            errors: {},
        }
    },

    computed: {
        isDirty() {
            return this.$dirty.has('base');
        },

        somethingIsLoading() {
            return !this.$progress.isComplete();
        },

        canSave() {
            return this.isDirty && !this.somethingIsLoading;
        },
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            if (!this.canSave) return;

            this.saving = true;
            this.clearErrors();

            const payload = this.hasOrigin
                ? { ...this.values, _localized: this.localizedFields }
                : this.values;

            this.$axios.patch(this.action, payload).then(response => {
                this.saving = false;
                this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.$emit('saved', response);
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                const message = data_get(e, 'response.data.message');
                this.$toast.error(message || e);
                console.log(e);
            }
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.isDirty) {
                if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
                    return;
                }
            }

            this.localizing = localization.handle;

            window.history.replaceState({}, '', localization.url);

            this.$axios.get(localization.url).then(response => {
                const data = response.data;
                this.action = data.action;
                this.deleteUrl = data.deleteUrl;
                this.listingUrl = data.listingUrl;
                this.values = data.values;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.hasOrigin = data.hasOrigin;
                this.originValues = data.originValues || {};
                this.originMeta = data.originMeta || {};
                this.localizedFields = data.localizedFields || [];
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.clearDirtyState());
            })
        },

        setFieldValue(handle, value) {
            if (this.hasOrigin) this.desyncField(handle);

            this.$refs.container.setFieldValue(handle, value);
        },

        syncField(handle) {
            if (! confirm(__('Are you sure? This field\'s value will be replaced by the value in the original entry.')))
                return;

            this.localizedFields = this.localizedFields.filter(field => field !== handle);
            this.$refs.container.setFieldValue(handle, this.originValues[handle]);

            this.meta[handle] = this.originMeta[handle];
        },

        desyncField(handle) {
            if (!this.localizedFields.includes(handle))
                this.localizedFields.push(handle);

            this.$refs.container.dirty();
        },

    },

    watch: {
        saving(saving) {
            this.$progress.loading('base-publish-form', saving);
        },
    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },
};
</script>

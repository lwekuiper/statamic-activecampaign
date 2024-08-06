<template>

    <div>
        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <site-selector
                    v-if="localizations.length > 1"
                    class="rtl:ml-4 ltr:mr-4"
                    :sites="localizations"
                    :value="site"
                    @input="localizationSelected"
                />

                <button
                    class="btn-primary min-w-100"
                    @click.prevent="save"
                    v-text="__('Save')" />
            </div>
        </header>

        <publish-container
            ref="container"
            name="base"
            :blueprint="blueprint"
            v-model="values"
            :meta="meta"
            :errors="errors"
            v-slot="{ setFieldValue, setFieldMeta }"
        >
            <publish-tabs
                @updated="setFieldValue"
                @meta-updated="setFieldMeta" />
        </publish-container>
    </div>

</template>

<script>
import SiteSelector from '../../../../vendor/statamic/cms/resources/js/components/SiteSelector.vue';

export default {

    components: {
        SiteSelector
    },

    props: {
        title: String,
        initialAction: String,
        method: String,
        blueprint: Object,
        initialMeta: Object,
        initialValues: Object,
        initialLocalizations: Array,
        initialSite: String,
    },

    data() {
        return {
            localizing: false,
            action: this.initialAction,
            meta: _.clone(this.initialMeta),
            values: _.clone(this.initialValues),
            localizations: _.clone(this.initialLocalizations),
            site: this.initialSite,
            error: null,
            errors: {},
        }
    },

    computed: {

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            if (!this.action) return;

            this.saving = true;
            this.clearErrors();

            this.$axios[this.method](this.action, this.values).then(response => {
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

            if (this.publishContainer === 'base') {
                window.history.replaceState({}, '', localization.url);
            }

            this.$axios.get(localization.url).then(response => {
                const data = response.data;
                this.action = data.action;
                this.values = data.values;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.clearDirtyState());
            })
        },

    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },
};
</script>

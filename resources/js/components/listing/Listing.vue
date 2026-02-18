<template>
    <div>

        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="__('ActiveCampaign')" />

                <site-selector
                    v-if="localizations.length > 1"
                    class="rtl:ml-4 ltr:mr-4"
                    :sites="localizations"
                    :value="site"
                    @input="localizationSelected"
                />

                <a :href="createFormUrl" class="btn-primary" v-text="__('Create Form')" />
            </div>
        </header>

        <data-list :rows="rows" :columns="columns">
            <div class="card overflow-hidden p-0">
                <data-list-table>
                    <template slot="cell-form" slot-scope="{ row: form }">
                        <a :href="form.edit_url">{{ form.title }}</a>
                    </template>
                    <template slot="actions" slot-scope="{ row: form }">
                        <dropdown-list>
                            <dropdown-item :text="__('Edit')" :redirect="form.edit_url" />
                        </dropdown-list>
                    </template>
                </data-list-table>
            </div>
        </data-list>

    </div>
</template>

<script>
import Listing from '../../../../vendor/statamic/cms/resources/js/components/Listing.vue'
import SiteSelector from '../../../../vendor/statamic/cms/resources/js/components/SiteSelector.vue';

export default {

    mixins: [Listing],

    components: {SiteSelector},

    props: {
        createFormUrl: { type: String, required: true },
        initialFormConfigs: { type: Array, required: true },
        initialLocalizations: { type: Array, required: true },
        initialSite: { type: String, required: true },
    },

    data() {
        return {
            rows: _.clone(this.initialFormConfigs),
            columns: [
                { label: __('Form'), field: 'form' },
                { label: __('Lists'), field: 'list_ids' },
                { label: __('Tags'), field: 'tag_ids' },
            ],
            localizations: _.clone(this.initialLocalizations),
            site: this.initialSite,
        }
    },

    methods: {
        localizationSelected(localization) {
            if (localization.active) return;

            this.loading = true;

            this.$axios.get(localization.url).then(response => {
                const data = response.data;
                this.rows = data.formConfigs;
                this.localizations = data.localizations;
                this.site = localization.handle;
                this.loading = false;
            })
        },
    },

}
</script>

<template>
    <div>

        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="__('ActiveCampaign')" />

                <dropdown-list v-if="configureUrl" class="rtl:ml-2 ltr:mr-2">
                    <dropdown-item :text="__('Configure')" :redirect="configureUrl" />
                </dropdown-list>

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
                    <template slot="cell-title" slot-scope="{ row: form }">
                        <div class="flex items-center">
                            <span class="little-dot rtl:ml-2 ltr:mr-2" :class="form.status === 'published' ? 'bg-green-600' : 'bg-gray-400'" />
                            <a :href="form.edit_url">{{ form.title }}</a>
                        </div>
                    </template>
                    <template slot="actions" slot-scope="{ row: form }">
                        <dropdown-list>
                            <dropdown-item :text="__('Edit')" :redirect="form.edit_url" />
                            <dropdown-item
                                v-if="form.delete_url"
                                :text="__('Delete')"
                                class="warning"
                                @click="$refs[`deleter-${form.title}`][0].confirm()"
                            >
                                <resource-deleter
                                    :ref="`deleter-${form.title}`"
                                    :resource-title="form.title"
                                    :route="form.delete_url"
                                    @deleted="removeRow(form)"
                                />
                            </dropdown-item>
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
        configureUrl: { type: String, default: null },
        initialFormConfigs: { type: Array, required: true },
        initialLocalizations: { type: Array, required: true },
        initialSite: { type: String, required: true },
    },

    data() {
        return {
            rows: _.clone(this.initialFormConfigs),
            columns: [
                { label: __('Form'), field: 'title' },
                { label: __('Lists'), field: 'lists' },
                { label: __('Tags'), field: 'tags' },
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

        removeRow(form) {
            const index = this.rows.indexOf(form);
            if (index > -1) {
                this.rows[index].delete_url = null;
                this.rows[index].status = 'draft';
                this.rows[index].lists = 0;
                this.rows[index].tags = 0;
            }
        },
    },

}
</script>

<template>
    <div class="list-field-mappings-fieldtype">
        <div v-if="loading" class="text-gray-600 text-sm p-2">
            {{ __('Loading...') }}
        </div>

        <div v-else-if="!subscriptionField" class="text-gray-600 text-sm p-2">
            {{ __('Select a form field first') }}
        </div>

        <div v-else-if="isMultiOptionField">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left pb-1 pr-4 text-2xs font-medium text-gray-700 uppercase tracking-wide">
                            {{ __('Value') }}
                        </th>
                        <th class="text-left pb-1 text-2xs font-medium text-gray-700 uppercase tracking-wide">
                            {{ __('ActiveCampaign List') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(mapping, index) in displayMappings" :key="mapping.subscription_value">
                        <td class="py-1 pr-4 text-sm text-gray-800 align-middle whitespace-nowrap">
                            {{ getOptionLabel(mapping.subscription_value) }}
                        </td>
                        <td class="py-1">
                            <v-select
                                append-to-body
                                :value="mapping.activecampaign_list_id"
                                :clearable="true"
                                :options="lists"
                                :reduce="(option) => option.id"
                                :placeholder="__('Choose...')"
                                :searchable="true"
                                @input="updateMapping(index, $event)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else>
            <v-select
                append-to-body
                :value="singleListId"
                :clearable="true"
                :options="lists"
                :reduce="(option) => option.id"
                :placeholder="__('Choose a list...')"
                :searchable="true"
                @input="updateSingleList($event)"
            />
        </div>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fieldOptions: {},
            lists: [],
            loading: true,
        }
    },

    computed: {
        form() {
            return this.meta.form || '';
        },

        subscriptionField() {
            const set = this.getReplicatorSet();
            return set?.values?.subscription_field || '';
        },

        fieldOptionsForField() {
            return this.fieldOptions[this.subscriptionField] || [];
        },

        isMultiOptionField() {
            return this.fieldOptionsForField.length > 0;
        },

        displayMappings() {
            if (!this.isMultiOptionField) return [];

            return this.fieldOptionsForField.map(option => {
                const existing = (this.value || []).find(
                    m => m.subscription_value === option.id
                );
                return {
                    subscription_value: option.id,
                    activecampaign_list_id: existing?.activecampaign_list_id || null,
                };
            });
        },

        singleListId() {
            if (this.isMultiOptionField) return null;
            const mappings = this.value || [];
            return mappings.length > 0 ? mappings[0].activecampaign_list_id : null;
        },
    },

    watch: {
        subscriptionField(newVal, oldVal) {
            if (newVal && oldVal && newVal !== oldVal) {
                this.$emit('input', []);
            }
        },
    },

    mounted() {
        Promise.all([
            this.refreshFieldOptions(),
            this.refreshLists(),
        ]).then(() => {
            this.loading = false;
        });
    },

    methods: {
        getReplicatorSet() {
            let parent = this.$parent;

            while (parent) {
                if (parent.values && parent.fields && parent.index !== undefined) {
                    return parent;
                }
                parent = parent.$parent;
            }

            return null;
        },

        refreshFieldOptions() {
            if (!this.form) return Promise.resolve();

            return this.$axios
                .get(cp_url(`/activecampaign/form-field-options/${this.form}`))
                .then(response => {
                    this.fieldOptions = response.data;
                })
                .catch(() => { this.fieldOptions = {}; });
        },

        refreshLists() {
            return this.$axios
                .get(cp_url('/activecampaign/lists'))
                .then(response => {
                    this.lists = response.data.map(list => ({
                        id: list.id,
                        label: list.label,
                    }));
                })
                .catch(() => { this.lists = []; });
        },

        getOptionLabel(value) {
            const option = this.fieldOptionsForField.find(o => o.id === value);
            return option ? option.label : value;
        },

        updateMapping(index, listId) {
            const mappings = this.displayMappings.map(m => ({ ...m }));
            mappings[index].activecampaign_list_id = listId;
            this.$emit('input', mappings.filter(m => m.activecampaign_list_id));
        },

        updateSingleList(listId) {
            this.$emit('input', listId ? [{ activecampaign_list_id: listId }] : []);
        },
    },
};
</script>

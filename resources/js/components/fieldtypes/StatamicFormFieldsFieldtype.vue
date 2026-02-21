<template>
    <div class="statamic-form-fields-fieldtype-wrapper">
        <v-select
            append-to-body
            v-model="value"
            :clearable="true"
            :options="fields"
            :reduce="(option) => option.id"
            :placeholder="__('Choose...')"
            :searchable="true"
            @input="$emit('input', $event)"
        >
            <template #no-options>{{ noOptionsText }}</template>
            <template v-if="showFieldType" #option="option">
                {{ option.label }} <span class="text-gray-600 text-2xs ml-2">{{ option.fieldtype }}</span>
            </template>
            <template v-if="showFieldType" #selected-option="option">
                {{ option.label }} <span class="text-gray-600 text-2xs ml-2">{{ option.fieldtype }}</span>
            </template>
        </v-select>
        <p class="text-yellow-dark text-2xs mt-2" v-if="hasStaleValue">
            {{ __('The selected field is not available in the current form.') }}
        </p>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fields: [],
            loaded: false,
        }
    },

    computed: {
        form() {
            return this.meta.form || '';
        },

        showFieldType() {
            return this.meta.showFieldType || false;
        },

        noOptionsText() {
            const filterLabels = {
                email: this.__('No email fields found.'),
                toggle: this.__('No toggle fields found.'),
            };

            const filter = this.meta.fieldFilter || null;

            return filterLabels[filter] || this.__('No fields found.');
        },

        hasStaleValue() {
            if (!this.loaded || !this.value) return false;

            return !this.fields.some(field => field.id === this.value);
        },
    },

    mounted() {
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            if (!this.form) return;

            const params = {};
            if (this.meta.fieldFilter) {
                params.filter = this.meta.fieldFilter;
            }

            this.$axios
                .get(cp_url(`/activecampaign/form-fields/${this.form}`), { params })
                .then(response => {
                    this.fields = response.data;
                    this.loaded = true;
                })
                .catch(() => {
                    this.fields = [];
                    this.loaded = true;
                });
        },
    }
};
</script>

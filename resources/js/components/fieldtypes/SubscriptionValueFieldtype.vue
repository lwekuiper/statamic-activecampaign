<template>
    <div class="subscription-value-fieldtype-wrapper">
        <v-select
            v-if="options.length"
            append-to-body
            v-model="value"
            :clearable="true"
            :options="options"
            :reduce="(option) => option.id"
            :placeholder="__('Choose...')"
            :searchable="true"
            @input="$emit('input', $event)"
        />
        <span v-else class="text-gray-500 text-sm">{{ __('Select a multi-option form field first') }}</span>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fieldOptions: {},
        }
    },

    computed: {
        form() {
            return this.meta.form || '';
        },

        subscriptionField() {
            const row = this.getGridRow();
            return row?.values?.subscription_field || '';
        },

        options() {
            return this.fieldOptions[this.subscriptionField] || [];
        },
    },

    watch: {
        subscriptionField() {
            if (this.value && !this.options.find(o => o.id === this.value)) {
                this.$emit('input', null);
            }
        },
    },

    mounted() {
        this.refreshFieldOptions();
    },

    methods: {
        getGridRow() {
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
            if (!this.form) return;

            this.$axios
                .get(cp_url(`/activecampaign/form-field-options/${this.form}`))
                .then(response => {
                    this.fieldOptions = response.data;
                })
                .catch(() => { this.fieldOptions = {}; });
        },
    }
};
</script>

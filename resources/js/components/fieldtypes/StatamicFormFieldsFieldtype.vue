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
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fields: [],
        }
    },

    computed: {
        form() {
            return this.meta.form || '';
        },

        fieldFilter() {
            return this.meta.fieldFilter || null;
        },
    },

    mounted() {
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            if (!this.form) return;

            const params = {};
            if (this.fieldFilter) {
                params.filter = this.fieldFilter;
            }

            this.$axios
                .get(cp_url(`/activecampaign/form-fields/${this.form}`), { params })
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        },
    }
};
</script>

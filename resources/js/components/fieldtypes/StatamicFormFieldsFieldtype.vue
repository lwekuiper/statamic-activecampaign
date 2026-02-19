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
    },

    mounted() {
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            if (!this.form) return;

            this.$axios
                .get(cp_url(`/activecampaign/form-fields/${this.form}`))
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        },
    }
};
</script>

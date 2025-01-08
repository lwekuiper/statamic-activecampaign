<template>
    <div class="activecampaign-merge-fields-fieldtype-wrapper">
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

    mounted() {
        this.refreshFields();
    },

    methods: {
        refreshFields() {
            this.$axios
                .get(cp_url('/activecampaign/merge-fields'))
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        }
    }
};
</script>

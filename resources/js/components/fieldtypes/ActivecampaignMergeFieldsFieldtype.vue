<template>
    <div class="activecampaign-merge-fields-fieldtype-wrapper">
        <v-select
            append-to-body
            v-if="showFieldtype"
            v-model="selected"
            :clearable="true"
            :options="fields"
            :reduce="(option) => option.id"
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
            selected: null,
            showFieldtype: true,
        }
    },

    mounted() {
        this.selected = this.value;
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

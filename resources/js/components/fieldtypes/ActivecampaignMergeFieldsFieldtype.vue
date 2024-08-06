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

    data(){
        return {
            fields: [],
            selected: null,
            showFieldtype: true,
        }
    },

    watch: {
        list(list) {
            this.showFieldtype = false;

            this.refreshFields();

            this.$nextTick(() => this.showFieldtype = true);
        }
    },

    computed: {
        key() {
            let matches = this.namePrefix.match(/([a-z]*?)\[(.*?)\]/);
            return matches[0].replace('[', '.').replace(']', '.') + 'list_id.0';
        },

        list() {
            return data_get(this.$store.state.publish[this.storeName].values, this.key)
        },
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
                });
        }
    }
};
</script>

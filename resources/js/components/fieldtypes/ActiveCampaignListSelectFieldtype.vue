<template>
    <div class="activecampaign-list-select-fieldtype-wrapper">
        <v-select
            append-to-body
            v-model="value"
            :clearable="true"
            :options="lists"
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

    data() {
        return {
            lists: [],
        }
    },

    mounted() {
        this.refreshLists();
    },

    methods: {
        refreshLists() {
            this.$axios
                .get(cp_url('/activecampaign/lists'))
                .then(response => {
                    this.lists = response.data.map(list => ({
                        id: list.id,
                        label: list.label,
                    }));
                })
                .catch(() => { this.lists = []; });
        },
    }
};
</script>

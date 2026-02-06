<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
});

const emit = defineEmits(['update:value']);

const selected = ref(null);
const fields = ref([]);

watch(selected, (val) => {
    emit('update:value', val);
});

onMounted(() => {
    selected.value = props.value;
    refreshFields();
});

function refreshFields() {
    axios
        .get(cp_url('/activecampaign/merge-fields'))
        .then(response => {
            fields.value = response.data;
        })
        .catch(() => { fields.value = []; });
}
</script>

<template>
    <div class="activecampaign-merge-fields-fieldtype-wrapper">
        <ui-combobox
            class="w-full"
            v-model="selected"
            :options="fields"
            optionValue="id"
            optionLabel="label"
            :label="__('Choose...')"
            :clearable="true"
            :searchable="true"
        />
    </div>
</template>

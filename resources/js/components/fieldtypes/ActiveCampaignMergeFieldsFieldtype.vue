<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
});

const emit = defineEmits(['update:value']);

const fields = ref([]);

onMounted(() => {
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
            :model-value="value"
            @update:model-value="emit('update:value', $event)"
            :options="fields"
            optionValue="id"
            optionLabel="label"
            :label="__('Choose...')"
            :clearable="true"
            :searchable="true"
        />
    </div>
</template>

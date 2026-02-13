<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
    meta: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:value']);

const fields = ref([]);

const form = computed(() => props.meta.form ?? '');

onMounted(() => {
    refreshFields();
});

function refreshFields() {
    axios
        .get(cp_url(`/activecampaign/form-fields/${form.value}`))
        .then(response => {
            fields.value = response.data;
        })
        .catch(() => { fields.value = []; });
}
</script>

<template>
    <div class="statamic-form-fields-fieldtype-wrapper">
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

<script setup>
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
    meta: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:value']);

const fieldOptions = ref({});

const form = computed(() => props.meta.form ?? '');

const selectedFormField = computed(() => {
    const row = getGridRow();
    return row?.values?.form_field ?? '';
});

const options = computed(() => fieldOptions.value[selectedFormField.value] ?? []);

watch(selectedFormField, () => {
    if (props.value && !options.value.find(o => o.id === props.value)) {
        emit('update:value', null);
    }
});

onMounted(() => {
    refreshFieldOptions();
});

function getGridRow() {
    const instance = getCurrentInstance();
    let parent = instance?.parent;

    while (parent) {
        if (parent.props?.values && parent.props?.fields && parent.props?.index !== undefined) {
            return { values: parent.props.values };
        }
        parent = parent.parent;
    }

    return null;
}

function refreshFieldOptions() {
    if (!form.value) return;

    axios
        .get(cp_url(`/activecampaign/form-field-options/${form.value}`))
        .then(response => {
            fieldOptions.value = response.data;
        })
        .catch(() => { fieldOptions.value = {}; });
}
</script>

<template>
    <div class="statamic-form-field-options-fieldtype-wrapper">
        <ui-combobox
            v-if="options.length"
            class="w-full"
            :model-value="value"
            @update:model-value="emit('update:value', $event)"
            :options="options"
            optionValue="id"
            optionLabel="label"
            :label="__('Choose...')"
            :clearable="true"
            :searchable="true"
        />
        <span v-else class="text-gray-500 text-sm">{{ __('Select a multi-option form field first') }}</span>
    </div>
</template>

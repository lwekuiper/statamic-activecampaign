<script setup>
import { ref, watch } from 'vue';
import { Switch, Heading, Select } from '@statamic/cms/ui';

const props = defineProps({
    value: { required: true },
});

const emit = defineEmits(['update:value']);

const sites = ref(props.value);

watch(sites, (val) => {
    emit('update:value', val);
}, { deep: true });

function siteOriginOptions(site) {
    return sites.value
        .filter((s) => s.enabled && s.handle !== site.handle)
        .map((s) => ({ value: s.handle, label: __(s.name) }));
}
</script>

<template>
    <table class="grid-table">
        <thead>
            <tr>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Site') }}
                    </div>
                </th>
                <th scope="col">
                    <div class="flex items-center justify-between">
                        {{ __('Origin') }}
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="site in sites" :key="site.handle">
                <td class="grid-cell">
                    <div class="flex items-center gap-2">
                        <Switch v-model="site.enabled" />
                        <Heading :text="__(site.name)" />
                    </div>
                </td>
                <td class="grid-cell">
                    <Select
                        class="w-full"
                        :options="siteOriginOptions(site)"
                        :clearable="true"
                        :model-value="site.origin"
                        @update:model-value="site.origin = $event"
                    />
                </td>
            </tr>
        </tbody>
    </table>
</template>

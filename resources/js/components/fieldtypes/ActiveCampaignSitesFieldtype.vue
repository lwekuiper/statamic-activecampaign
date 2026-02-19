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
            <tr v-for="(site, index) in sites" :key="site.handle">
                <td>
                    <div class="flex items-center text-sm">
                        <toggle-input
                            class="rtl:ml-4 ltr:mr-4"
                            :value="site.enabled"
                            @input="toggleSite(index, $event)"
                        />
                        {{ __(site.name) }}
                    </div>
                </td>
                <td class="text-sm">
                    <v-select
                        class="w-full"
                        :clearable="true"
                        :options="siteOriginOptions(site)"
                        :reduce="(option) => option.value"
                        :value="site.origin"
                        @input="setOrigin(index, $event)"
                    />
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            sites: _.clone(this.value),
        }
    },

    watch: {
        sites: {
            handler(val) {
                this.update(val);
            },
            deep: true,
        },
    },

    methods: {
        siteOriginOptions(site) {
            return this.sites
                .filter((s) => s.handle !== site.handle)
                .map((s) => ({ value: s.handle, label: __(s.name) }));
        },

        toggleSite(index, enabled) {
            this.sites[index].enabled = enabled;
        },

        setOrigin(index, origin) {
            this.sites[index].origin = origin;
        },
    },
};
</script>

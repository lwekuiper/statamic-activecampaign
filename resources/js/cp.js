import PublishForm from './components/publish/PublishForm.vue';
import MergeFieldsField from './components/fieldtypes/ActivecampaignMergeFieldsFieldtype.vue';
import FormFieldsField from './components/fieldtypes/FormFieldsFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('activecampaign-publish-form', PublishForm);
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('form_fields-fieldtype', FormFieldsField);
});

import ActiveCampaignListing from './components/listing/ActiveCampaignListing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import MergeFieldsField from './components/fieldtypes/ActiveCampaignMergeFieldsFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import Index from './pages/Index.vue';
import Empty from './pages/Empty.vue';
import Edit from './pages/Edit.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('activecampaign::Index', Index);
    Statamic.$inertia.register('activecampaign::Empty', Empty);
    Statamic.$inertia.register('activecampaign::Edit', Edit);

    Statamic.$components.register('activecampaign-listing', ActiveCampaignListing);
    Statamic.$components.register('activecampaign-publish-form', PublishForm);
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});

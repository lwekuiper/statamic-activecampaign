import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import MergeFieldsField from './components/fieldtypes/ActiveCampaignMergeFieldsFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import SitesField from './components/fieldtypes/ActiveCampaignSitesFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('activecampaign-listing', Listing);
    Statamic.$components.register('activecampaign-publish-form', PublishForm);
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
    Statamic.$components.register('activecampaign_sites-fieldtype', SitesField);
});

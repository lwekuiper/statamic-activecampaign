import MergeFieldsField from './components/fieldtypes/ActiveCampaignMergeFieldsFieldtype.vue';
import SitesField from './components/fieldtypes/ActiveCampaignSitesFieldtype.vue';
import FormFieldOptionsField from './components/fieldtypes/StatamicFormFieldOptionsFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';

Statamic.booting(() => {
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('activecampaign_sites-fieldtype', SitesField);
    Statamic.$components.register('activecampaign-listing', Listing);
    Statamic.$components.register('activecampaign-publish-form', PublishForm);
    Statamic.$components.register('statamic_form_field_options-fieldtype', FormFieldOptionsField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
});

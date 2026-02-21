import Listing from './components/listing/Listing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import ListSelectField from './components/fieldtypes/ActiveCampaignListSelectFieldtype.vue';
import MergeFieldsField from './components/fieldtypes/ActiveCampaignMergeFieldsFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import SitesField from './components/fieldtypes/ActiveCampaignSitesFieldtype.vue';
import SubscriptionValueField from './components/fieldtypes/SubscriptionValueFieldtype.vue';
import ListFieldMappingsField from './components/fieldtypes/ListFieldMappingsFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('activecampaign-listing', Listing);
    Statamic.$components.register('activecampaign-publish-form', PublishForm);
    Statamic.$components.register('activecampaign_list_select-fieldtype', ListSelectField);
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', MergeFieldsField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
    Statamic.$components.register('activecampaign_sites-fieldtype', SitesField);
    Statamic.$components.register('subscription_value-fieldtype', SubscriptionValueField);
    Statamic.$components.register('list_field_mappings-fieldtype', ListFieldMappingsField);
});

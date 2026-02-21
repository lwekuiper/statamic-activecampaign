<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ActiveCampaign API URL
    |--------------------------------------------------------------------------
    |
    | The API URL is required to make requests to the ActiveCampaign API.
    | You can find this URL in your ActiveCampaign account settings.
    | It typically follows the format: "https://youraccount.api-us1.com"
    |
    */

    'api_url' => env('ACTIVECAMPAIGN_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | ActiveCampaign API Key
    |--------------------------------------------------------------------------
    |
    | The API key is necessary to authenticate your application with
    | ActiveCampaign. You can find this key in the API section of
    | your ActiveCampaign account settings.
    |
    */

    'api_key' => env('ACTIVECAMPAIGN_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Filter Form Fields
    |--------------------------------------------------------------------------
    |
    | When enabled, the email field selector will only show text fields with
    | input type "email", and the consent field selector will only show
    | toggle fields. Set to false to show all form fields.
    |
    */

    'filter_form_fields' => true,

];

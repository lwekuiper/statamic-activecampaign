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

];

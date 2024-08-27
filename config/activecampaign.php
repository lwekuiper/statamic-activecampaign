<?php

return [

    /**
     * ActiveCampaign API URL
     */
    'api_url' => env('ACTIVECAMPAIGN_API_URL'),

    /**
     * ActiveCampaign API Key
     */
    'api_key' => env('ACTIVECAMPAIGN_API_KEY'),

    /**
     * The form submissions to add to your ActiveCampaign lists
     */
    'forms' => [
        [
            /**
             * The form handle.
             */
            'form' => '',

            /**
             * Field name that contains the email.
             */
            'email_field' => '',

            /**
             * Field name that contains the consent.
             */
            'consent_field' => '',

            /**
             * A ActiveCampaign list.
             */
            'list_id' => '',

            /**
             * A ActiveCampaign tag.
             */
            'tag' => '',

            /**
             * Merge fields to add to the contact.
             */
            'merge_fields' => [],
        ]
    ],

    /**
     * The multi-site forms to add to your ActiveCampaign lists - requires pro edition
     */
    'sites' => [
        'default' => [
            [
                'form' => '',

                'email_field' => '',

                'consent_field' => '',

                'list_id' => '',

                'tag' => '',

                'merge_fields' => [],
            ]
        ]
    ]

];

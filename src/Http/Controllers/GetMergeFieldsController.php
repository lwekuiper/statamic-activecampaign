<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Support\Arr;
use Statamic\Http\Controllers\Controller;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class GetMergeFieldsController extends Controller
{
    public function __invoke(): array
    {
        $standardFields = [
            [
                'id' => 'email',
                'label' => 'Email'
            ],
            [
                'id' => 'firstName',
                'label' => 'First Name'
            ],
            [
                'id' => 'lastName',
                'label' => 'Last Name'
            ],
            [
                'id' => 'phone',
                'label' => 'Phone'
            ]
        ];

        $response = ActiveCampaign::listCustomFields();

        $customFields = collect(Arr::get($response, 'fields', []))
            ->map(fn ($customField) => [
                'id' => $customField['id'],
                'label' => $customField['title']
            ])
            ->values()
            ->all();

        return array_merge($standardFields, $customFields);
    }
}

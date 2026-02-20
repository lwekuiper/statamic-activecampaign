<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Statamic\Support\Arr;
use Statamic\Http\Controllers\Controller;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class GetListsController extends Controller
{
    public function __invoke(): array
    {
        if (! ActiveCampaign::isConfigured()) {
            return [];
        }

        $response = ActiveCampaign::getLists();

        return collect(Arr::get($response, 'lists', []))
            ->map(fn ($list) => [
                'id' => $list['id'],
                'label' => $list['name'],
            ])
            ->values()
            ->all();
    }
}

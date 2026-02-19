<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Support\Arr;
use Statamic\Fieldtypes\Relationship;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class ActiveCampaignList extends Relationship
{
    public static function handle()
    {
        return 'activecampaign_list';
    }

    public function getIndexItems($request)
    {
        if (! ActiveCampaign::isConfigured()) {
            abort(403, __('ActiveCampaign API credentials are not configured.'));
        }

        $lists = Arr::get(ActiveCampaign::getLists(), 'lists', []);

        return collect($lists)->map(fn ($list) => [
            'id' => $list['id'],
            'title' => $list['name']
        ])->toArray();
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        $lists = Arr::get(ActiveCampaign::getLists(), 'lists', []);
        $list = collect($lists)->firstWhere('id', $id);

        if (! $list) {
            return [];
        }

        return [
            'id' => $list['id'],
            'title' => $list['name'],
        ];
    }
}

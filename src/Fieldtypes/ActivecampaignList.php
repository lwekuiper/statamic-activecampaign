<?php

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Support\Arr;
use Statamic\Fieldtypes\Relationship;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class ActivecampaignList extends Relationship
{
    public function getIndexItems($request)
    {
        $response = ActiveCampaign::getLists();

        $lists = Arr::get($response, 'lists', []);

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

        if (! $list = Arr::get(ActiveCampaign::getList($id), 'list')) {
            return [];
        }

        return [
            'id' => $list['id'],
            'title' => $list['name'],
        ];
    }
}

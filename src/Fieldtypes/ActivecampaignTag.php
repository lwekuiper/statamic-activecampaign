<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Fieldtypes;

use Statamic\Support\Arr;
use Statamic\Fieldtypes\Relationship;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class ActiveCampaignTag extends Relationship
{
    public static function handle()
    {
        return 'activecampaign_tag';
    }

    public function getIndexItems($request)
    {
        $response = ActiveCampaign::listTags();

        $tags = Arr::get($response, 'tags', []);

        return collect($tags)->map(fn ($tag) => [
            'id' => $tag['id'],
            'title' => $tag['tag']
        ])->toArray();
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        if (! $list = Arr::get(ActiveCampaign::getTag($id), 'tag')) {
            return [];
        }

        return [
            'id' => $list['id'],
            'title' => $list['tag'],
        ];
    }
}

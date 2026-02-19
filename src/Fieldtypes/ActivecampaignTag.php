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
        if (! ActiveCampaign::isConfigured()) {
            abort(403, __('ActiveCampaign API credentials are not configured.'));
        }

        $tags = Arr::get(ActiveCampaign::getTags(), 'tags', []);

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

        $tags = Arr::get(ActiveCampaign::getTags(), 'tags', []);
        $tag = collect($tags)->firstWhere('id', $id);

        if (! $tag) {
            return [];
        }

        return [
            'id' => $tag['id'],
            'title' => $tag['tag'],
        ];
    }
}

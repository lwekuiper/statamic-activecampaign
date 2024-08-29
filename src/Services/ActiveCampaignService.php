<?php

namespace Lwekuiper\StatamicActivecampaign\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class ActiveCampaignService
{
    protected $baseUrl;
    protected $key;

    public function __construct()
    {
        $this->baseUrl = config('statamic.activecampaign.api_url');
        $this->key = config('statamic.activecampaign.api_key');
    }

    public function syncContact($data)
    {
        return $this->client()->post('contact/sync', [
            'contact' => $data
        ]);
    }

    public function getLists()
    {
        return $this->client()->get('lists');
    }

    public function getList($id)
    {
        return $this->client()->get("lists/{$id}");
    }

    public function listTags()
    {
        return $this->client()->get('tags');
    }

    public function getTag($id)
    {
        return $this->client()->get("tags/{$id}");
    }

    public function listCustomFields()
    {
        return $this->client()->get('fields');
    }

    public function client(): PendingRequest
    {
        return Http::withHeaders([
            'Api-Token' => $this->key,
        ])
            ->acceptJson()
            ->baseUrl("{$this->baseUrl}/api/3/");
    }
}

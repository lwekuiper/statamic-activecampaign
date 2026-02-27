<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\Blink;

class ActiveCampaignConnector
{
    protected $baseUrl;
    protected $key;

    public function __construct()
    {
        $this->baseUrl = config('statamic.activecampaign.api_url');
        $this->key = config('statamic.activecampaign.api_key');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->key);
    }

    public function syncContact($email, $data): array
    {
        $contact = array_merge(['email' => $email], $data);

        return $this->client()->post('contact/sync', [
            'contact' => $contact,
        ])->json();
    }

    public function updateListStatus($contactId, $listId): array
    {
        return $this->client()->post('contactLists', [
            'contactList' => [
                'list' => $listId,
                'contact' => $contactId,
                'status' => 1,
            ],
        ])->json();
    }

    public function addTagToContact($contactId, $tagId): array
    {
        return $this->client()->post('contactTags', [
            'contactTag' => [
                'contact' => $contactId,
                'tag' => $tagId,
            ],
        ])->json();
    }

    public function getLists(): array
    {
        return Blink::once('activecampaign::lists', function () {
            return $this->client()->get('lists', ['limit' => -1])->json();
        });
    }

    public function getList($id): array
    {
        return Blink::once("activecampaign::list::{$id}", function () use ($id) {
            return $this->client()->get("lists/{$id}")->json();
        });
    }

    public function getTags(): array
    {
        return Blink::once('activecampaign::tags', function () {
            return $this->client()->get('tags', ['limit' => -1])->json();
        });
    }

    public function getTag($id): array
    {
        return Blink::once("activecampaign::tag::{$id}", function () use ($id) {
            return $this->client()->get("tags/{$id}")->json();
        });
    }

    public function getCustomFields(): array
    {
        return Blink::once('activecampaign::custom-fields', function () {
            return $this->client()->get('fields')->json();
        });
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Api-Token' => $this->key,
        ])
            ->acceptJson()
            ->throw()
            ->baseUrl("{$this->baseUrl}/api/3/");
    }
}

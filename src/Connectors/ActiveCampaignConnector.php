<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    public function syncContact($email, $data): ?array
    {
        $contact = array_merge(['email' => $email], $data);

        $response = $this->client()->post('contact/sync', [
            'contact' => $contact
        ]);

        return $this->handleResponse($response, 'Failed to sync contact', $data);
    }

    public function updateListStatus($contactId, $listId): ?array
    {
        $response = $this->client()->post('contactLists', [
            'contactList' => [
                'list' => $listId,
                'contact' => $contactId,
                'status' => 1
            ]
        ]);

        return $this->handleResponse($response, 'Failed to update list status', [
            'contact_id' => $contactId,
            'list_id' => $listId
        ]);
    }

    public function addTagToContact($contactId, $tagId): ?array
    {
        $response = $this->client()->post('contactTags', [
            'contactTag' => [
                'contact' => $contactId,
                'tag' => $tagId
            ]
        ]);

        return $this->handleResponse($response, 'Failed to add tag to contact', [
            'contact_id' => $contactId,
            'tag_id' => $tagId
        ]);
    }

    public function getLists(): ?array
    {
        return Blink::once('activecampaign::lists', function () {
            $response = $this->client()->get('lists', ['limit' => -1]);

            return $this->handleResponse($response, 'Failed to get lists');
        });
    }

    public function getList($id): ?array
    {
        return Blink::once("activecampaign::list::{$id}", function () use ($id) {
            $response = $this->client()->get("lists/{$id}");

            return $this->handleResponse($response, 'Failed to get list', ['id' => $id]);
        });
    }

    public function getTags(): ?array
    {
        return Blink::once('activecampaign::tags', function () {
            $response = $this->client()->get('tags', ['limit' => -1]);

            return $this->handleResponse($response, 'Failed to get tags');
        });
    }

    public function getTag($id): ?array
    {
        return Blink::once("activecampaign::tag::{$id}", function () use ($id) {
            $response = $this->client()->get("tags/{$id}");

            return $this->handleResponse($response, 'Failed to get tag', ['id' => $id]);
        });
    }

    public function getCustomFields(): ?array
    {
        return Blink::once('activecampaign::custom-fields', function () {
            $response = $this->client()->get('fields');

            return $this->handleResponse($response, 'Failed to get custom fields');
        });
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Api-Token' => $this->key,
        ])
            ->acceptJson()
            ->baseUrl("{$this->baseUrl}/api/3/");
    }

    private function handleResponse(Response $response, string $errorMessage, array $context = []): ?array
    {
        if (! $response->successful()) {
            Log::error($errorMessage, array_merge([$response->json()], $context));

            return null;
        }

        return $response->json();
    }
}

<?php

namespace Lwekuiper\StatamicActivecampaign\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
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
        $response = $this->client()->get('lists');

        return $this->handleResponse($response, 'Failed to get lists');
    }

    public function getList($id): ?array
    {
        $response = $this->client()->get("lists/{$id}");

        return $this->handleResponse($response, 'Failed to get list', ['id' => $id]);
    }

    public function listTags(): ?array
    {
        $response = $this->client()->get('tags');

        return $this->handleResponse($response, 'Failed to list tags');
    }

    public function getTag($id): ?array
    {
        $response = $this->client()->get("tags/{$id}");

        return $this->handleResponse($response, 'Failed to get tag', ['id' => $id]);
    }

    public function listCustomFields(): ?array
    {
        $response = $this->client()->get('fields');

        return $this->handleResponse($response, 'Failed to list custom fields');
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

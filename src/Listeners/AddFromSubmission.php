<?php

namespace Lwekuiper\StatamicActivecampaign\Listeners;

use Statamic\Facades\Site;
use Illuminate\Support\Arr;
use Statamic\Facades\Addon;
use Statamic\Forms\Submission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Statamic\Events\SubmissionCreated;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class AddFromSubmission
{
    private Collection $data;

    private Collection $config;

    public function __construct()
    {
        $this->data = collect();
        $this->config = collect();
    }

    public function shouldHandle(Submission $submission)
    {
        $configKey = 'forms';

        $edition = Addon::get('lwekuiper/statamic-activecampaign')->edition();

        if ($edition === 'pro') {
            $site = Site::findByUrl(URL::previous()) ?? Site::default();
            $configKey = "sites.{$site->handle()}";
        }

        $this->config = collect(Arr::first(
            config("statamic.activecampaign.{$configKey}", []),
            fn (array $formConfig) => $formConfig['form'] == $submission->form()->handle()
        ));

        if ($this->config->isEmpty()) {
            return false;
        }

        $this->data = collect($submission->data());

        return $this->hasConsent();
    }

    public function hasConsent(): bool
    {
        $field = $this->config->get('consent_field', 'consent');

        return filter_var(
            Arr::get(Arr::wrap($this->data->get($field, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function handle(SubmissionCreated $event): void
    {
        if (! $this->shouldHandle($event->submission)) {
            return;
        }

        // Create or update contact.
        $contact = $this->syncContact();

        // Exit if no contact was created or updated.
        if (! $contact) return;

        // Get contact ID.
        $contactId = $contact['contact']['id'];

        // Update list status for contact.
        $this->updateListStatus($contactId);

        // Add optional tag to contact.
        if ($this->config->has('tag')) {
            $this->addTagToContact($contactId);
        }
    }

    private function syncContact(): ?array
    {
        $email = $this->data->get('email');
        $mergeData = $this->getMergeData();

        return ActiveCampaign::syncContact($email, $mergeData);
    }

    private function getMergeData(): array
    {
        $mergeFields = $this->config->get('merge_fields', []);

        [$standardFields, $customFields] = collect($mergeFields)->partition(function ($item) {
            return in_array($item['activecampaign_field'], ['email', 'firstName', 'lastName', 'phone']);
        });

        $standardData = $standardFields->mapWithKeys(function ($item) {
            return [$item['activecampaign_field'] => $this->data->get($item['field_name'])];
        })->filter()->all();

        $customData = $customFields->map(function ($item) {
            return [
                'field' => $item['activecampaign_field'],
                'value' => $this->data->get($item['field_name'])
            ];
        })->filter()->values()->all();

        return array_merge($standardData, ['fieldValues' => $customData]);
    }

    private function updateListStatus($contactId): void
    {
        $listId = $this->config->get('list_id');

        ActiveCampaign::updateListStatus($contactId, $listId);
    }

    private function addTagToContact($contactId): void
    {
        $tagId = $this->config->get('tag');

        ActiveCampaign::addTagToContact($contactId, $tagId);
    }
}

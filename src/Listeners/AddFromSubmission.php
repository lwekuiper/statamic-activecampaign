<?php

namespace Lwekuiper\StatamicActivecampaign\Listeners;

use Statamic\Facades\Site;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Statamic\Events\SubmissionCreated;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;
use Statamic\Forms\Submission;

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
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        $this->config = collect(Arr::first(
            config("statamic.activecampaign.sites.{$site->handle()}.forms", []),
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

        $mergeData = $this->getMergeData();

        $listId = $this->config->get('list_id');

        $contact = array_merge([
            'email' => $this->data->get('email'),
            "p[{$listId}]" => $listId,
            "status[{$listId}]" => 1,
            'tags' => $this->config->get('tag', ''),
        ], $mergeData);

        $response = ActiveCampaign::createOrUpdateContact($contact);

        if (is_string($response)) {
            Log::error('Syncing contact failed. Error returned: ' . $response);
        }
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
}

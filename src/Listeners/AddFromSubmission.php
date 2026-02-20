<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Listeners;

use Statamic\Facades\Site;
use Illuminate\Support\Arr;
use Statamic\Facades\Addon;
use Statamic\Forms\Submission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Statamic\Events\SubmissionCreated;
use Lwekuiper\StatamicActivecampaign\Data\FormConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicActivecampaign\Facades\ActiveCampaign;

class AddFromSubmission
{
    private Collection $data;

    private ?FormConfig $config;

    public function __construct()
    {
        $this->data = collect();
        $this->config = null;
    }

    public function getEmail(): string
    {
        return $this->data->get($this->config?->value('email_field') ?? 'email');
    }

    public function hasFormConfig(Submission $submission): bool
    {
        $edition = Addon::get('lwekuiper/statamic-activecampaign')->edition();

        $site = $edition === 'pro'
            ? Site::findByUrl(URL::previous()) ?? Site::default()
            : Site::default();

        $resolved = FormConfigFacade::findResolved($submission->form()->handle(), $site->handle());

        if (! $resolved) {
            return false;
        }

        if ($resolved->values()->isEmpty()) {
            return false;
        }

        $this->data = collect($submission->data());
        $this->config = $resolved;

        return true;
    }

    public function hasConsent(): bool
    {
        if (! $field = $this->config?->value('consent_field')) {
            return true;
        }

        return filter_var(
            Arr::get(Arr::wrap($this->data->get($field, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function getSubscribedListFieldIds(): array
    {
        $listFields = $this->config->value('list_fields') ?? [];

        return collect($listFields)
            ->filter(function ($row) {
                $fieldData = $this->data->get($row['subscription_field'] ?? '');
                $subscriptionValue = $row['subscription_value'] ?? null;

                if ($subscriptionValue !== null && $subscriptionValue !== '') {
                    return in_array($subscriptionValue, Arr::wrap($fieldData));
                }

                return filter_var(
                    Arr::get(Arr::wrap($fieldData), 0, false),
                    FILTER_VALIDATE_BOOLEAN
                );
            })
            ->pluck('activecampaign_list_id')
            ->filter()
            ->values()
            ->all();
    }

    public function handle(SubmissionCreated $event): void
    {
        if (! $this->hasFormConfig($event->submission)) {
            return;
        }

        if (! $this->hasConsent()) {
            return;
        }

        $listMode = $this->config->value('list_mode') ?? 'fixed';

        $fixedListIds = in_array($listMode, ['fixed', 'both'])
            ? ($this->config->value('list_ids') ?? [])
            : [];

        $dynamicListIds = in_array($listMode, ['dynamic', 'both'])
            ? $this->getSubscribedListFieldIds()
            : [];

        $allListIds = array_unique(array_merge($fixedListIds, $dynamicListIds));

        if (empty($allListIds) && empty($this->config->value('tag_ids'))) {
            return;
        }

        if (! $contact = $this->syncContact()) {
            return;
        }

        $contactId = Arr::get($contact, 'contact.id');

        foreach ($allListIds as $listId) {
            $this->updateListStatus($contactId, $listId);
        }

        foreach ($this->config->value('tag_ids') ?? [] as $tagId) {
            $this->addTagToContact($contactId, $tagId);
        }
    }

    private function syncContact(): ?array
    {
        $email = $this->getEmail();
        $mergeData = $this->getMergeData();

        return ActiveCampaign::syncContact($email, $mergeData);
    }

    private function getMergeData(): array
    {
        $mergeFields = $this->config->value('merge_fields') ?? [];

        [$standardFields, $customFields] = collect($mergeFields)->partition(function ($item) {
            return in_array($item['activecampaign_field'], ['email', 'firstName', 'lastName', 'phone']);
        });

        $standardData = $standardFields->mapWithKeys(function ($item) {
            return [$item['activecampaign_field'] => $this->data->get($item['statamic_field'])];
        })->filter()->all();

        $customData = $customFields->map(function ($item) {
            $fieldValue = $this->data->get($item['statamic_field']);

            if (is_array($fieldValue)) {
                $fieldValue = implode(', ', array_filter($fieldValue));
            }

            return [
                'field' => $item['activecampaign_field'],
                'value' => (string) $fieldValue
            ];
        })->filter(fn ($item) => $item['value'] !== '')->values()->all();

        return array_merge($standardData, ['fieldValues' => $customData]);
    }

    private function updateListStatus($contactId, $listId): void
    {
        ActiveCampaign::updateListStatus($contactId, $listId);
    }

    private function addTagToContact($contactId, $tagId): void
    {
        ActiveCampaign::addTagToContact($contactId, $tagId);
    }
}

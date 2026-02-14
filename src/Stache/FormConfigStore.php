<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Stache;

use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;

class FormConfigStore extends BasicStore
{
    protected $storeIndexes = [
        'handle',
        'locale',
    ];

    public function key()
    {
        return 'form-configs';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        $formConfig = FormConfig::make()
            ->initialPath($path)
            ->emailField(Arr::pull($data, 'email_field'))
            ->consentField(Arr::pull($data, 'consent_field'))
            ->listId(Arr::pull($data, 'list_id'))
            ->tagId(Arr::pull($data, 'tag_id'))
            ->mergeFields(Arr::pull($data, 'merge_fields', []));

        $handle = explode('/', $handle);
        if (count($handle) > 1) {
            $formConfig->form($handle[1])
                ->locale($handle[0]);
        } else {
            $formConfig->form($handle[0])
                ->locale(Site::default()->handle());
        }

        return $formConfig;
    }

    public function getItemKey($item)
    {
        return "{$item->handle()}::{$item->locale()}";
    }
}

<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Stache;

use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Symfony\Component\Finder\SplFileInfo;
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

    public function getItemFilter(SplFileInfo $file)
    {
        if ($file->getExtension() !== 'yaml') {
            return false;
        }

        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        if ($filename === 'config.yaml' || Str::endsWith($filename, '/config.yaml')) {
            return false;
        }

        $slashes = substr_count($filename, '/');

        return $slashes === 0 || $slashes === 1;
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        // Migrate legacy singular keys to plural
        if (! isset($data['list_ids']) && isset($data['list_id'])) {
            $data['list_ids'] = Arr::wrap($data['list_id']);
        }
        unset($data['list_id']);

        if (! isset($data['tag_ids']) && isset($data['tag_id'])) {
            $data['tag_ids'] = Arr::wrap($data['tag_id']);
        }
        unset($data['tag_id']);

        $formConfig = FormConfig::make()
            ->initialPath($path)
            ->data($data);

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

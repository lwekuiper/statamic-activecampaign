<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Http\Request;
use Lwekuiper\StatamicActivecampaign\Facades\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class AddonConfigController extends CpController
{
    public function edit()
    {
        if (! $this->isMultiSite()) {
            return redirect()->to(cp_route('activecampaign.index'));
        }

        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        $blueprint = $this->blueprint();

        $values = [];

        if ($this->isMultiSite()) {
            $values['sites'] = Site::all()->map(fn ($site) => [
                'name' => $site->name(),
                'handle' => $site->handle(),
                'enabled' => AddonConfig::isEnabled($site->handle()),
                'origin' => AddonConfig::originFor($site->handle()),
            ])->values()->all();
        }

        $fields = $blueprint->fields()->addValues($values)->preProcess();

        $viewData = [
            'title' => __('Configure ActiveCampaign'),
            'action' => cp_route('activecampaign.update'),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];

        if ($request = request()) {
            if ($request->wantsJson()) {
                return $viewData;
            }
        }

        return view('statamic-activecampaign::configure', $viewData);
    }

    public function update(Request $request)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        $fields = $this->blueprint()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->all();

        if ($this->isMultiSite()) {
            $previousSites = AddonConfig::sites()->keys();

            $newSites = collect($values['sites'])
                ->filter(fn ($site) => $site['enabled'])
                ->mapWithKeys(fn ($site) => [$site['handle'] => $site['origin']]);

            AddonConfig::save($newSites);

            $this->syncFormConfigs($previousSites->all(), $newSites->keys()->all());
        } else {
            AddonConfig::save(collect([Site::default()->handle() => null]));
        }

        return response('', 204);
    }

    private function syncFormConfigs(array $previousSites, array $newSites): void
    {
        $addedSites = array_diff($newSites, $previousSites);
        $removedSites = array_diff($previousSites, $newSites);

        // Create empty form configs for newly enabled sites
        if (! empty($addedSites)) {
            $existingHandles = collect($previousSites)
                ->flatMap(fn ($site) => FormConfig::whereLocale($site)->map->handle())
                ->unique()
                ->all();

            foreach ($addedSites as $site) {
                foreach ($existingHandles as $handle) {
                    if (! FormConfig::find($handle, $site)) {
                        FormConfig::make()->form($handle)->locale($site)->save();
                    }
                }
            }
        }

        // Delete form configs for disabled sites
        foreach ($removedSites as $site) {
            FormConfig::whereLocale($site)->each->delete();
        }
    }

    private function isMultiSite(): bool
    {
        return Site::multiEnabled()
            && Addon::get('lwekuiper/statamic-activecampaign')->edition() === 'pro';
    }

    protected function blueprint()
    {
        $sections = [];

        if ($this->isMultiSite()) {
            $sections[] = [
                'fields' => [
                    [
                        'handle' => 'sites',
                        'field' => [
                            'type' => 'activecampaign_sites',
                            'required' => true,
                        ],
                    ],
                ],
            ];
        }

        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => $sections,
                ],
            ],
        ]);
    }
}

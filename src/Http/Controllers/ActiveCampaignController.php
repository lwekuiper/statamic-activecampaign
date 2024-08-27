<?php

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Statamic\Facades\Site;
use Illuminate\Support\Arr;
use Statamic\Facades\Addon;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\Controller;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class ActiveCampaignController extends Controller
{
    public function edit(Request $request)
    {
        $site = $request->site ?? Site::selected()->handle();

        $edition = $this->getEdition();

        $blueprint = $this->getBlueprint();

        $values = $this->getValues($edition, $site);

        $fields = $blueprint->fields()->addValues($values)->preProcess();

        $viewData = [
            'edition' => $edition,
            'blueprint' => $blueprint->toPublishArray(),
            'action' => cp_route('activecampaign.update'),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'action' => cp_route('activecampaign.update', ['site' => $site]),
                'site' => $site,
                'localizations' => Site::all()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('activecampaign.edit', ['site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-activecampaign::edit', $viewData);
    }

    public function update(Request $request)
    {
        $site = $request->site ?? Site::selected()->handle();

        $blueprint = $this->getBlueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $data = $fields->process()->values()->toArray();

        $edition = $this->getEdition();

        $key = $this->getConfigKey($edition, $site);

        ConfigWriter::write("statamic.activecampaign.{$key}", $this->postProcess($data));

        return response()->json(['saved' => true]);
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint()
    {
        return Blueprint::find('statamic-activecampaign::config');
    }

    /**
     * Get the edition of the addon.
     */
    private function getEdition(): string
    {
        return Addon::get('lwekuiper/statamic-activecampaign')->edition();
    }

    /**
     * Post process the values.
     */
    private function postProcess(array $values): array
    {
        return Arr::get($values, 'forms', []);
    }

    /**
     * Get the values from the config.
     */
    private function getValues($edition, $site): array
    {
        $key = $this->getConfigKey($edition, $site);

        return ['forms' => config("statamic.activecampaign.{$key}", [])];
    }

    /**
     * Get the config key based on the edition and site.
     */
    private function getConfigKey($edition, $site): string
    {
        return $edition === 'pro' ? "sites.{$site}" : 'forms';
    }
}

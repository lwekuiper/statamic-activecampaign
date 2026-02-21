<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Http\Request;
use Lwekuiper\StatamicActivecampaign\Facades\AddonConfig;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as BlueprintContract;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\CP\CpController;

class FormConfigController extends CpController
{
    public function index(Request $request)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        [$site, $edition] = $this->getAddonContext($request);

        $urlParams = $edition === 'pro' ? ['site' => $site] : [];

        $forms = FormFacade::all();

        $formConfigs = $forms->map(function ($form) use ($urlParams, $site) {
            $localConfig = FormConfig::find($form->handle(), $site);
            $resolved = FormConfig::findResolved($form->handle(), $site);

            $resolvedValues = $resolved?->values() ?? collect();
            $resolvedListIds = $resolvedValues->get('list_ids', []);
            $resolvedListFields = collect($resolvedValues->get('list_fields', []))
                ->flatMap(fn ($set) => $set['list_mappings'] ?? [])
                ->all();
            $resolvedTagIds = $resolvedValues->get('tag_ids', []);

            $hasLocalData = $localConfig !== null && ! $localConfig->data()->isEmpty();
            $hasValues = $resolvedValues->filter()->isNotEmpty();

            return [
                'title' => $form->title(),
                'edit_url' => cp_route('activecampaign.form-config.edit', ['form' => $form->handle(), ...$urlParams]),
                'lists' => count($resolvedListIds) + count($resolvedListFields),
                'tags' => count($resolvedTagIds),
                'delete_url' => $hasLocalData ? cp_route('activecampaign.form-config.destroy', ['form' => $form->handle(), ...$urlParams]) : null,
                'status' => $hasValues ? 'published' : 'draft',
            ];
        })->values();

        $viewData = [
            'formConfigs' => $formConfigs,
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('activecampaign.index', ['site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        $configureUrl = $this->isMultiSite() ? cp_route('activecampaign.edit') : null;

        return view('statamic-activecampaign::index', array_merge($viewData, [
            'configureUrl' => $configureUrl,
        ]));
    }

    public function edit(Request $request, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $formConfig = FormConfig::find($form->handle(), $site);

        $hasOrigin = $edition === 'pro' && $formConfig && $formConfig->hasOrigin();

        if ($hasOrigin) {
            $originValues = $formConfig->origin()->values()->all();
            $displayValues = $formConfig->values()->all();

            $fields = $blueprint->fields()->addValues($displayValues)->preProcess();

            [$originValues, $originMeta] = $this->extractFromFields($originValues, $blueprint);
            $localizedFields = $formConfig->data()->keys()->all();
        } else {
            $fields = $blueprint->fields();

            if ($formConfig) {
                $fields = $fields->addValues($formConfig->data()->all());
            }

            $fields = $fields->preProcess();
        }

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('activecampaign.form-config.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route('activecampaign.index', ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizedFields' => $localizedFields ?? [],
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'origin' => ! AddonConfig::hasOrigin($localization->handle()),
                    'url' => cp_route('activecampaign.form-config.edit', ['form' => $form->handle(), 'site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-activecampaign::edit', $viewData);
    }

    public function update(Request $request, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values();

        $hasOrigin = $edition === 'pro' && AddonConfig::hasOrigin($site);

        if ($hasOrigin) {
            $values = $values->only($request->input('_localized', []));
        }

        $values = $values->all();

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig->data($values);

        $formConfig->save();

        if ($edition === 'pro') {
            FormConfig::ensureLocalizationsExist($form->handle());
        }

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        if ($formConfig->hasOrigin()) {
            $formConfig->data(collect())->save();
        } else {
            $formConfig->delete();
        }

        return response('', 204);
    }

    private function extractFromFields(array $values, BlueprintContract $blueprint): array
    {
        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }

    /**
     * Get the site and edition based on the request.
     */
    private function getAddonContext(Request $request): array
    {
        $edition = Addon::get('lwekuiper/statamic-activecampaign')->edition();

        $site = $edition === 'pro'
            ? $request->site ?? Site::selected()->handle()
            : Site::default()->handle();

        return [$site, $edition];
    }

    /**
     * Get sites where ActiveCampaign is enabled.
     */
    private function getEnabledSites(): \Illuminate\Support\Collection
    {
        return Site::all()->filter(fn ($site) => AddonConfig::isEnabled($site->handle()));
    }

    private function isMultiSite(): bool
    {
        return Site::multiEnabled()
            && Addon::get('lwekuiper/statamic-activecampaign')->edition() === 'pro';
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
    {
        return Blueprint::find('statamic-activecampaign::config');
    }
}

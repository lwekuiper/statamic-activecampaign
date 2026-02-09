<?php

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
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

        $forms = FormFacade::all()
            ->mapWithKeys(fn ($form) => [
                $form->handle() => [
                    'title' => $form->title(),
                    'edit_url' => cp_route('activecampaign.edit', ['form' => $form->handle(), ...$urlParams]),
                ]
            ]);

        $formConfigs = FormConfig::whereLocale($site)
            ->mapWithKeys(fn ($formConfig) => [
                $formConfig->handle() => [
                    'list_ids' => $formConfig->listIds(),
                    'tag_ids' => $formConfig->tagIds(),
                    'delete_url' => $formConfig->deleteUrl(),
                ]
            ]);

        $viewData = [
            'formConfigs' => $forms->mergeRecursive($formConfigs)->values()
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => Site::all()->map(fn ($localization) => [
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

        if ($viewData['formConfigs']->isEmpty()) {
            return Inertia::render('activecampaign::Empty', [
                'createUrl' => cp_route('forms.create'),
            ]);
        }

        return Inertia::render('activecampaign::Index', [
            'createFormUrl' => cp_route('forms.create'),
            'formConfigs' => $viewData['formConfigs'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
        ]);
    }

    public function edit(Request $request, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields();

        if ($formConfig = FormConfig::find($form->handle(), $site)) {
            $fields = $fields->addValues($formConfig->fileData());
        }

        $fields = $fields->preProcess();

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('activecampaign.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route('activecampaign.index', ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => Site::all()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('activecampaign.edit', ['form' => $form->handle(), 'site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return Inertia::render('activecampaign::Edit', [
            'title' => $viewData['title'],
            'action' => $viewData['action'],
            'deleteUrl' => $viewData['deleteUrl'],
            'listingUrl' => $viewData['listingUrl'],
            'blueprint' => $viewData['blueprint'],
            'values' => $viewData['values'],
            'meta' => $viewData['meta'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
        ]);
    }

    public function update(Request $request, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values()->all();

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig = $formConfig
            ->emailField($values['email_field'])
            ->consentField($values['consent_field'])
            ->listIds($values['list_ids'])
            ->tagIds($values['tag_ids'])
            ->mergeFields($values['merge_fields']);

        $formConfig->save();

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        $formConfig->delete();

        return response('', 204);
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
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
    {
        return Blueprint::find('statamic-activecampaign::config');
    }

}

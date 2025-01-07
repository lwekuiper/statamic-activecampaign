<?php

namespace Lwekuiper\StatamicActivecampaign\Http\Controllers;

use Illuminate\Http\Request;
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

        $edition = $this->getEdition();
        $site = $this->getSite($edition, $request);

        $formConfigs = FormConfig::whereLocale($site)
            ->map(fn ($config) => [
                'id' => $config->handle(),
                'list_id' => $config->listId(),
                'tag_id' => $config->tagId(),
            ]);

        $forms = FormFacade::all()
            ->map(function ($form) use ($formConfigs, $site) {
                $config = $formConfigs->firstWhere('id', $form->handle());

                return [
                    'id' => $form->handle(),
                    'title' => $form->title(),
                    'list_id' => data_get($config, 'list_id'),
                    'tag_id' => data_get($config, 'tag_id'),
                    'edit_url' => cp_route('activecampaign.edit', ['form' => $form->handle(), 'site' => $site]),
                    'deleteable' => $config !== null,
                    'delete_url' => cp_route('activecampaign.destroy', ['form' => $form->handle(), 'site' => $site]),
                ];
            })->filter()->values();

        $viewData = ['forms' => $forms];

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

        return view('statamic-activecampaign::index', $viewData);
    }

    public function edit(Request $request, Form $form)
    {
        $edition = $this->getEdition();
        $site = $this->getSite($edition, $request);

        $formConfig = FormConfig::find($form->handle(), $site);
        $values = $formConfig?->fileData() ?? [];

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields();
        $fields = $formConfig ? $fields->addValues($values) : $fields;
        $fields = $fields->preProcess();

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('activecampaign.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'site' => $site,
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
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

        return view('statamic-activecampaign::edit', $viewData);
    }

    public function update(Request $request, Form $form)
    {
        $edition = $this->getEdition();
        $site = $this->getSite($edition, $request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values()->all();

        $formConfig = FormConfig::find($form->handle(), $site);

        if (! $formConfig) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig = $formConfig
            ->emailField($values['email_field'])
            ->consentField($values['consent_field'])
            ->listId($values['list_id'])
            ->tagId($values['tag_id'])
            ->mergeFields($values['merge_fields']);

        $formConfig->save();

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        $edition = $this->getEdition();
        $site = $this->getSite($edition, $request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        $formConfig->delete();

        return response('', 204);
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
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
     * Get the site based on the edition.
     */
    private function getSite($edition, $request)
    {
        if ($edition === 'pro') {
            return $request->site ?? Site::selected()->handle();
        }

        return Site::default()->handle();
    }
}
